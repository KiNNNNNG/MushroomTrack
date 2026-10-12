<?php

namespace App\Http\Controllers;

use App\Models\Notice;
use Illuminate\Http\Request;

class NoticeController extends Controller
{
    /** Mark one notification read, then go to the page it points to. */
    public function open(Request $request, Notice $notice)
    {
        abort_unless($notice->user_id === $request->user()->id, 403);
        $notice->update(['read_at' => $notice->read_at ?? now()]);

        return $notice->url ? redirect($notice->url) : back();
    }

    public function readAll(Request $request)
    {
        Notice::where('user_id', $request->user()->id)->whereNull('read_at')->update(['read_at' => now()]);

        return back();
    }
}
