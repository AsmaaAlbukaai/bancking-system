<?php

namespace App\Modules\Support;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SupportController extends Controller
{
    /**
     * إنشاء تذكرة
     */
    public function createTicket(Request $request)
    {
        $request->validate([
            'type' => 'required|in:inquiry,complaint',
            'subject' => 'required|string',
            'message' => 'required|string',
        ]);

        $ticket = SupportTicket::create([
            'user_id' => auth()->id(),
            'type' => $request->type,
            'subject' => $request->subject,
            'message' => $request->message,
            'assigned_to_role' => $request->type === 'inquiry' ? 'teller' : 'manager',
        ]);

        return response()->json([
            'message' => 'Ticket created successfully.',
            'ticket' => $ticket,
        ]);
    }

    /**
     * رد الموظف على تذكرة
     */
    public function reply(Request $request, $ticketId)
    {
        $ticket = SupportTicket::findOrFail($ticketId);

        if (auth()->user()->role !== $ticket->assigned_to_role) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $reply = TicketReply::create([
            'ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'message' => $request->message,
        ]);

        $ticket->update([
            'status' => 'answered',
            'last_reply_by' => auth()->id(),
        ]);

        return response()->json(['message' => 'Reply added.', 'reply' => $reply]);
    }

    /**
     * إغلاق التذكرة
     */
    public function closeTicket($ticketId)
    {
        $ticket = SupportTicket::findOrFail($ticketId);

        if ($ticket->user_id !== auth()->id() && auth()->user()->role !== 'manager') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $ticket->update(['status' => 'closed']);

        return response()->json(['message' => 'Ticket closed.']);
    }


    public function managerComplaints()
{
    $this->authorizeRole(['manager']);

    $tickets = SupportTicket::with('replies.user')
        ->where('assigned_to_role', 'manager')
        ->orderBy('created_at', 'desc')
        ->get();

    return response()->json($tickets);
}


    public function tellerInquiries()
{
    $this->authorizeRole(['teller']);

    $tickets = SupportTicket::with('replies.user')
        ->where('assigned_to_role', 'teller')
        ->orderBy('created_at', 'desc')
        ->get();

    return response()->json($tickets);
}
  
    public function userTickets()
{
    $tickets = SupportTicket::with('replies.user')
        ->where('user_id', auth()->id())
        ->orderBy('created_at', 'desc')
        ->get();

    return response()->json($tickets);
}
       
    private function authorizeRole(array $allowedRoles)
{
    $userRole = auth()->user()->role;

    if (!in_array($userRole, $allowedRoles)) {
        abort(403, 'Unauthorized');
    }
}

}
