<?php

namespace App\Console\Commands;

use App\Models\Blog;
use App\Models\Cause;
use App\Models\SosAlert;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:remove-demo-content')]
#[Description('Removes exactly the records created by app:seed-demo-content, matched on their known slugs/titles so real content is never touched. Ops use, no arguments needed.')]
class RemoveDemoContent extends Command
{
    /** Slugs created by SeedDemoContent. */
    private const CAUSE_SLUGS = [
        'rebuild-the-pandharpur-community-well',
        'solapur-girls-school-library-fund',
        'emergency-medical-fund-for-accident-victim',
    ];

    private const BLOG_SLUGS = [
        'how-unity-verifies-every-cause-before-it-goes-live',
        'the-rs199-activation-fee-where-it-actually-goes',
        'meet-the-volunteers-behind-the-pandharpur-sos-network',
    ];

    private const SOS_TITLES = [
        'O-negative blood needed urgently — Solapur Civil Hospital',
        'Insulin (Lantus) needed for elderly patient — Pandharpur',
    ];

    public function handle(): int
    {
        $causes = Cause::query()->whereIn('slug', self::CAUSE_SLUGS)->delete();
        $blogs = Blog::query()->whereIn('slug', self::BLOG_SLUGS)->delete();
        $sos = SosAlert::query()->whereIn('title', self::SOS_TITLES)->delete();

        $this->info("Demo content removed: {$causes} causes, {$blogs} blogs, {$sos} SOS alerts.");

        return self::SUCCESS;
    }
}
