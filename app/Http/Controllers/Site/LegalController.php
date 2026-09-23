<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;

class LegalController extends Controller
{
    public function privacy()
    {
        return $this->render('privacy', __('site.legal.privacy_title'));
    }

    public function terms()
    {
        return $this->render('terms', __('site.legal.terms_title'));
    }

    public function data()
    {
        return $this->render('data', __('site.legal.data_title'));
    }

    public function refund()
    {
        return $this->render('refund', __('site.legal.refund_title'));
    }

    public function childSafety()
    {
        return $this->render('child-safety', __('site.legal.child_safety_title'));
    }

    private function render(string $page, string $title)
    {
        $locale = app()->getLocale();
        $view = "site.legal.{$page}-{$locale}";

        if (! view()->exists($view)) {
            $view = "site.legal.{$page}-en";
        }

        return view('site.legal.show', ['bodyView' => $view, 'title' => $title]);
    }
}
