<?php

namespace App\Console\Commands;

use App\Models\Blog;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:seed-platform-blogs')]
#[Description('Idempotent: publishes the two factual posts describing how Unity itself works (post review and the activation fee), and removes the older cause/business-listing-themed posts these replace. Safe for production — these describe real platform policy, not invented community activity.')]
class SeedPlatformBlogs extends Command
{
    /** Slugs of retired posts this command used to publish under an earlier positioning. */
    private const RETIRED_SLUGS = [
        'how-unity-verifies-every-cause-before-it-goes-live',
        'how-unity-verifies-every-business-listing-before-it-goes-live',
    ];

    public function handle(): int
    {
        $author = User::role('admin')->first();

        if (! $author) {
            $this->error('No admin user found to attach posts to.');

            return self::FAILURE;
        }

        $removed = Blog::query()->whereIn('slug', self::RETIRED_SLUGS)->delete();

        $posts = [
            [
                'title' => 'How Unity Reviews Every Post Before It Goes Live',
                'excerpt' => "Transparency isn't optional in a closed community — here's our review process.",
                'content' => "Every post on Unity goes through a manual review before it is published. Our team checks that it comes from a verified member in good standing, and only then does it go live.\n\nWe would rather have fewer posts and full trust than an open, unmoderated feed. Review usually takes 24 to 48 hours.\n\nPosting is limited to members our admin team has approved as authors. The same applies to SOS alerts, which are restricted to approved authors. This is deliberate: an open posting system would be faster, but it would also make the community trivial to abuse.",
            ],
            [
                'title' => 'The ₹199 Activation Fee — Where It Actually Goes',
                'excerpt' => 'No ads, no data-selling, no venture funding pressure — just a small one-time fee to keep fake accounts out.',
                'content' => "Unity runs on a simple idea: a small one-time activation fee keeps the platform free of fake accounts, and free of the advertising incentives that push most social platforms toward outrage and addiction.\n\nThe ₹199 fee funds server costs, SMS and OTP delivery, and payment processing. There is nothing left over for advertising, because there are no ads to sell — and we do not sell member data.\n\nActivation is calculated by our systems, not claimed manually. Your profile always shows exactly where you stand.",
            ],
        ];

        foreach ($posts as $data) {
            Blog::query()->updateOrCreate(
                ['slug' => str($data['title'])->slug()->toString()],
                [
                    'author_id' => $author->id,
                    'title' => $data['title'],
                    'excerpt' => $data['excerpt'],
                    'content' => $data['content'],
                    'status' => 'published',
                    'published_at' => now(),
                ]
            );
        }

        $this->info("Retired posts removed: {$removed}. Platform blog posts published: ".count($posts).'.');

        return self::SUCCESS;
    }
}
