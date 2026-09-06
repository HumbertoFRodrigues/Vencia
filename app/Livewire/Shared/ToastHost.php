<?php

namespace App\Livewire\Shared;

use Livewire\Component;

/**
 * Mounted once in layouts.app. Entirely stateless on the PHP side — it just
 * renders a fixed host div and registers a JS listener (via @script, so it
 * survives Livewire's morphing/wire:navigate) for the browser 'toast' event
 * that any component dispatches via $this->dispatch('toast', title: ...,
 * body: ..., tone: ...). Building toasts purely in JS (rather than
 * appending to a PHP-side array + re-rendering) means they appear
 * instantly and auto-dismiss on a client-side timer without needing a
 * network round trip for the dismiss.
 */
class ToastHost extends Component
{
    public function render()
    {
        return view('livewire.shared.toast-host');
    }
}
