<?php

namespace App\Console\Commands;

use App\Models\Blog;
use App\Models\Cause;
use App\Models\SosAlert;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:seed-demo-content')]
#[Description('Idempotent: creates a handful of published Causes/Blogs and active SOS alerts so the mobile app has something to show. Ops/demo use, no arguments needed.')]
class SeedDemoContent extends Command
{
    public function handle(): int
    {
        $author = User::role('admin')->first();

        if (! $author) {
            $this->error('No admin user found to attach demo content to.');

            return self::FAILURE;
        }

        $causes = [
            [
                'title' => 'Rebuild the Pandharpur Community Well',
                'excerpt' => 'The only well serving 40 families in Bhoyare Wadi collapsed after the monsoon — help us rebuild it before summer.',
                'content' => "The community well in Bhoyare Wadi, Pandharpur, has served over 40 families for two generations. Heavy monsoon rains this year caused the retaining wall to collapse, leaving the well unsafe to use.\n\nWe're raising funds to rebuild the wall, install a proper cover, and add a hand pump so the well stays safe and usable for years to come. Every contribution — no matter how small — brings us closer to clean water for these families before the summer heat sets in.",
                'goal_amount' => 150000,
                'raised_amount' => 62000,
                'verified' => true,
                'featured_image' => 'https://picsum.photos/seed/unity-well/900/600',
            ],
            [
                'title' => "Solapur Girls' School Library Fund",
                'excerpt' => 'Help us stock a school library with 500 books for 300 students who currently share a handful of torn textbooks.',
                'content' => "Zilla Parishad Girls' School in Solapur has never had a proper library. Students share worn textbooks, and there are no story books, reference material, or exam-prep guides available.\n\nWe've partnered with the school to build a small library room and stock it with 500 books across subjects and grade levels. This cause covers shelving, books, and a part-time librarian's stipend for the first year.",
                'goal_amount' => 80000,
                'raised_amount' => 80000,
                'verified' => true,
                'featured_image' => 'https://picsum.photos/seed/unity-library/900/600',
            ],
            [
                'title' => 'Emergency Medical Fund for Accident Victim',
                'excerpt' => "Ramesh, a rickshaw driver and father of two, needs urgent surgery after a road accident. His family can't cover the cost alone.",
                'content' => "Ramesh Kadam, a 34-year-old auto-rickshaw driver from Solapur, was seriously injured in a road accident last week. He needs immediate orthopedic surgery to avoid permanent disability, but the family has already exhausted their savings on initial hospital admission.\n\nAs the sole earning member supporting his wife and two young children, Ramesh's recovery means everything to this family. Every rupee raised goes directly to the hospital, verified through his medical records.",
                'goal_amount' => 120000,
                'raised_amount' => 34500,
                'verified' => false,
                'featured_image' => 'https://picsum.photos/seed/unity-medical/900/600',
            ],
        ];

        foreach ($causes as $data) {
            Cause::query()->updateOrCreate(
                ['slug' => str($data['title'])->slug()->toString()],
                [
                    'organizer_id' => $author->id,
                    'title' => $data['title'],
                    'excerpt' => $data['excerpt'],
                    'content' => $data['content'],
                    'featured_image' => $data['featured_image'],
                    'goal_amount' => $data['goal_amount'],
                    'raised_amount' => $data['raised_amount'],
                    'status' => 'published',
                    'verified' => $data['verified'],
                ]
            );
        }

        $blogs = [
            [
                'title' => 'How Unity Verifies Every Cause Before It Goes Live',
                'excerpt' => "Transparency isn't optional when people are trusting strangers with their money — here's our verification process.",
                'content' => "Every cause on Unity goes through a manual review before it's published. Our team checks the organizer's identity, verifies the situation directly where possible (hospital records, school confirmations, local references), and only then does a cause go live with the option to display a 'Verified by admin' badge.\n\nWe'd rather have fewer causes and full trust than a flood of unverified requests. If you're an organizer, this review usually takes 24-48 hours.",
                'featured_image' => 'https://picsum.photos/seed/unity-verify/900/600',
            ],
            [
                'title' => 'The ₹199 Activation Fee — Where It Actually Goes',
                'excerpt' => 'No ads, no data-selling, no venture funding pressure — just a small one-time fee to keep fake accounts out.',
                'content' => "Unity runs on a simple idea: a small one-time activation fee keeps the platform free of fake accounts and free of the ad-driven incentives that push most social platforms toward outrage and addiction.\n\nThe ₹199 fee, combined with two paid referrals, funds server costs, SMS/OTP delivery, and payment processing — with nothing left over for advertising, because there are no ads to sell.",
                'featured_image' => 'https://picsum.photos/seed/unity-fee/900/600',
            ],
            [
                'title' => 'Meet the Volunteers Behind the Pandharpur SOS Network',
                'excerpt' => 'A small group of approved authors keeps emergency blood and medication requests moving fast across the district.',
                'content' => "Behind every SOS alert on Unity is an approved author who has personally verified the emergency — a real hospital admission, a real blood requirement, a real family in need.\n\nThis network of local volunteers is what keeps SOS alerts fast and trustworthy instead of becoming another feed people learn to scroll past. We're always looking for more verified community members to join as approved authors.",
                'featured_image' => 'https://picsum.photos/seed/unity-volunteers/900/600',
            ],
        ];

        foreach ($blogs as $data) {
            Blog::query()->updateOrCreate(
                ['slug' => str($data['title'])->slug()->toString()],
                [
                    'author_id' => $author->id,
                    'title' => $data['title'],
                    'excerpt' => $data['excerpt'],
                    'content' => $data['content'],
                    'featured_image' => $data['featured_image'],
                    'status' => 'published',
                    'published_at' => now()->subDays(random_int(1, 14)),
                ]
            );
        }

        $sosAlerts = [
            [
                'category' => 'blood',
                'title' => 'O-negative blood needed urgently — Solapur Civil Hospital',
                'description' => 'A patient undergoing emergency surgery tonight needs 2 units of O-negative blood. Please contact directly if you can donate or know a donor.',
                'location' => 'Solapur Civil Hospital, Solapur',
                'contact_info' => '+91 98765 43210',
            ],
            [
                'category' => 'medication',
                'title' => 'Insulin (Lantus) needed for elderly patient — Pandharpur',
                'description' => "An elderly diabetic patient's family has run out of Lantus insulin and the local pharmacy is out of stock. Any spare vials or leads on availability nearby would help immensely.",
                'location' => 'Pandharpur, Solapur District',
                'contact_info' => '+91 91234 56789',
            ],
        ];

        foreach ($sosAlerts as $data) {
            SosAlert::query()->updateOrCreate(
                ['title' => $data['title']],
                [
                    'author_id' => $author->id,
                    'category' => $data['category'],
                    'description' => $data['description'],
                    'location' => $data['location'],
                    'contact_info' => $data['contact_info'],
                    'status' => 'active',
                    'expires_at' => now()->addDays(3),
                ]
            );
        }

        $this->info('Demo content seeded: '.count($causes).' causes, '.count($blogs).' blogs, '.count($sosAlerts).' SOS alerts.');

        return self::SUCCESS;
    }
}
