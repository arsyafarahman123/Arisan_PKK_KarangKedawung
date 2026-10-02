<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\ArisanGroup;
use App\Models\ArisanRound;
use App\Models\GroupMember;
use App\Models\NotificationLog;
use App\Models\Payment;
use App\Models\User;
use App\Services\ArisanService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Run check on overdue penalties
        ArisanService::checkAllOverduePenalties();

        if ($user->isAdmin()) {
            return $this->adminDashboard();
        }

        return $this->memberDashboard($user);
    }

    private function adminDashboard()
    {
        $totalGroups = ArisanGroup::count();
        $activeGroups = ArisanGroup::where('status', 'active')->count();
        $totalMembers = User::where('role', 'member')->where('is_active', true)->count();
        
        $totalMoneyCollected = Payment::where('payment_status', 'paid')->sum('amount');
        $totalPenalties = Payment::where('payment_status', 'paid')->sum('penalty_amount');
        $pendingVerificationsCount = Payment::where('payment_status', 'pending_verification')->count();
        $pendingPayments = Payment::where('payment_status', 'pending_verification')
            ->with(['user', 'group', 'round'])
            ->latest('paid_at')
            ->take(5)
            ->get();

        $activeRounds = ArisanRound::where('status', 'ongoing')
            ->with(['group', 'host', 'payments'])
            ->get();

        $upcomingDraws = ArisanRound::whereIn('status', ['ongoing', 'pending'])
            ->where('draw_date', '>=', now()->toDateString())
            ->orderBy('draw_date')
            ->take(4)
            ->with(['group', 'host'])
            ->get();

        $unpaidOverduePayments = Payment::whereIn('payment_status', ['unpaid', 'late'])
            ->whereHas('round', function ($q) {
                $q->where('status', 'ongoing');
            })
            ->with(['user', 'group', 'round'])
            ->orderBy('due_date')
            ->take(8)
            ->get();

        $recentActivities = ActivityLog::with('user')->latest()->take(6)->get();

        $recentWinners = ArisanRound::where('status', 'completed')
            ->with(['winner', 'group'])
            ->latest('draw_date')
            ->take(5)
            ->get();

        return view('dashboard.admin', compact(
            'totalGroups',
            'activeGroups',
            'totalMembers',
            'totalMoneyCollected',
            'totalPenalties',
            'pendingVerificationsCount',
            'pendingPayments',
            'activeRounds',
            'upcomingDraws',
            'unpaidOverduePayments',
            'recentActivities',
            'recentWinners'
        ));
    }

    private function memberDashboard(User $user)
    {
        // Groups joined by this member
        $memberships = GroupMember::where('user_id', $user->id)
            ->where('is_active', true)
            ->with(['group.rounds', 'wonRound'])
            ->get();

        // Active bills for this user
        $myBills = Payment::where('user_id', $user->id)
            ->whereIn('payment_status', ['unpaid', 'pending_verification', 'late'])
            ->with(['group', 'round.host'])
            ->orderBy('due_date')
            ->get();

        // Payment history
        $paymentHistory = Payment::where('user_id', $user->id)
            ->where('payment_status', 'paid')
            ->with(['group', 'round'])
            ->latest('paid_at')
            ->take(6)
            ->get();

        // Recent won rounds
        $myWonRounds = ArisanRound::where('winner_user_id', $user->id)
            ->with('group')
            ->latest('draw_date')
            ->get();

        // Next upcoming arisan rounds for groups joined
        $groupIds = $memberships->pluck('group_id');
        $upcomingEvents = ArisanRound::whereIn('group_id', $groupIds)
            ->whereIn('status', ['ongoing', 'pending'])
            ->orderBy('draw_date')
            ->take(4)
            ->with(['group', 'host'])
            ->get();

        $notifications = NotificationLog::where('user_id', $user->id)
            ->latest()
            ->take(6)
            ->get();

        return view('dashboard.member', compact(
            'user',
            'memberships',
            'myBills',
            'paymentHistory',
            'myWonRounds',
            'upcomingEvents',
            'notifications'
        ));
    }
}
