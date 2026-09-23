<?php

namespace App\Console\Commands;

use App\Models\Blog;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:seed-platform-blogs')]
#[Description('Idempotent: publishes the two factual posts describing how Unity itself works (listing verification and the activation fee), and removes the older cause/fundraiser-themed post this replaces. Safe for production — these describe real platform policy, not invented community activity.')]
class SeedPlatformBlogs extends Command
{
    /** Slug of the retired cause-themed post this command used to publish. */
    private const RETIRED_SLUG = 'how-unity-verifies-every-cause-before-it-goes-live';

    public function handle(): int
    {
        $author = User::role('admin')->first();

        if (! $author) {
            $this->error('No admin user found to attach posts to.');

            return self::FAILURE;
        }

        $removed = Blog::query()->where('slug', self::RETIRED_SLUG)->delete();

        $posts = [
            [
                'title' => 'How Unity Verifies Every Business Listing Before It Goes Live',
                'excerpt' => "Transparency isn't optional when people are choosing who to trust — here's our verification process.",
                'content' => "Every business listing on Unity goes through a manual review before it is published. Our team checks the owner's identity, verifies the business is real wherever possible, and only then does a listing go live. Listings that clear this check can display a 'Verified by admin' badge.\n\nWe would rather have fewer listings and full trust than a flood of unverified entries. If you are a business owner, this review usually takes 24 to 48 hours.\n\nPosting a listing is limited to members our admin team has approved as authors. The same applies to SOS alerts, which are restricted to approved authors. This is deliberate: an open posting system would be faster, but it would also make the platform trivial to abuse.",
            ],
            [
                'title' => 'The ₹199 Activation Fee — Where It Actually Goes',
                'excerpt' => 'No ads, no data-selling, no venture funding pressure — just a small one-time fee to keep fake accounts out.',
                'content' => "Unity runs on a simple idea: a small one-time activation fee keeps the platform free of fake accounts, and free of the advertising incentives that push most social platforms toward outrage and addiction.\n\nThe ₹199 fee, combined with two paid referrals, funds server costs, SMS and OTP delivery, and payment processing. There is nothing left over for advertising, because there are no ads to sell — and we do not sell member data.\n\nActivation is calculated by our systems, not claimed manually. Your profile always shows exactly where you stand: whether the fee is paid, and how many of the people you referred have joined and paid theirs.",
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
