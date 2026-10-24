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

        return back()->with('ok', 'All notifications marked as read.');
    }

    /** Delete a single notification. */
    public function destroy(Request $request, Notice $notice)
    {
        abort_unless($notice->user_id === $request->user()->id, 403);
        $notice->delete();

        return back()->with('ok', 'Notification deleted successfully.');
    }

    /** Bulk delete selected notifications. */
    public function destroySelected(Request $request)
    {
        $ids = $request->input('selected_ids', []);
        if (is_string($ids)) {
            $ids = explode(',', $ids);
        }
        $ids = array_filter(array_map('intval', (array) $ids));

        if (!empty($ids)) {
            $count = Notice::where('user_id', $request->user()->id)
                ->whereIn('id', $ids)
                ->delete();

            return back()->with('ok', "{$count} notification(s) deleted successfully.");
        }

        return back()->with('error', 'No notifications selected.');
    }
}
