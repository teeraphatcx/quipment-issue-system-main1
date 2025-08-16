<?php

namespace App\Http\Controllers;

use App\Models\Issue;
use App\Models\Building;
use App\Models\Room;
use App\Models\Equipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class IssueController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin')->only([
            'index', 'reply', 'submitReply', 'dashboard'
        ]);
    }

    // แดชบอร์ด admin
    public function dashboard()
    {
        $total = Issue::count();
        $pending = Issue::where('status', 'pending')->count();
        $replied = Issue::where('status', 'replied')->count();

        return view('admin.dashboard', compact('total', 'pending', 'replied'));
    }

    // ฟอร์มสร้างแจ้งปัญหา
    public function create()
    {
        $buildings = Building::all();
        $rooms = Room::all();
        $equipments = Equipment::all();

        return view('issues.create', compact('buildings', 'rooms', 'equipments'));
    }

    // บันทึกแจ้งปัญหา
    public function store(Request $request)
    {
        $validated = $request->validate([
            'building'    => 'required|string|max:255',
            'room'        => 'required|string|max:255',
            'equipment'   => 'required|string|max:255',
            'description' => 'nullable|string',
            'email'       => 'required|email',
            'image'       => 'nullable|image|mimes:jpg,jpeg,png,gif|max:5120',
        ]);

        $issue = new Issue($validated);
        $issue->status = 'pending';

        if ($request->hasFile('image')) {
            $issue->image_path = $request->file('image')->store('issues', 'public');
        }

        $issue->save();

        // แจ้งเตือน Telegram
        $this->sendTelegramNotification($issue);

        return redirect()->back()->with('success', 'ส่งข้อมูลเรียบร้อยแล้ว');
    }

    // รายการแจ้งปัญหา (Admin)
    public function index(Request $request)
    {
        $query = Issue::query();

        if ($request->filled('keyword')) {
            $query->where(function ($q) use ($request) {
                $q->where('building', 'like', '%' . $request->keyword . '%')
                    ->orWhere('room', 'like', '%' . $request->keyword . '%')
                    ->orWhere('equipment', 'like', '%' . $request->keyword . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $issues = $query->latest()->paginate(10);

        return view('admin.issues.index', compact('issues'));
    }

    // เปลี่ยนสถานะ
    public function toggleStatus($id)
    {
        $issue = Issue::findOrFail($id);
        $issue->status = $issue->status === 'pending' ? 'replied' : 'pending';
        $issue->save();

        return redirect()->back()->with('success', 'เปลี่ยนสถานะเรียบร้อยแล้ว');
    }

    // ลบแจ้งปัญหา
    public function destroy($id)
    {
        $issue = Issue::findOrFail($id);
        $issue->delete();

        return redirect()->back()->with('success', 'ลบรายการเรียบร้อยแล้ว');
    }

    // ตอบกลับปัญหา (Admin)
    public function reply($id)
    {
        $issue = Issue::findOrFail($id);
        return view('admin.issues.reply', compact('issue'));
    }

    public function submitReply(Request $request, $id)
    {
        $request->validate([
            'admin_reply' => 'required|string',
        ]);

        $issue = Issue::findOrFail($id);
        $issue->admin_reply = $request->admin_reply;
        $issue->status = 'replied';
        $issue->save();

        return redirect()->route('admin.issues.index')->with('success', 'ตอบกลับเรียบร้อยแล้ว');
    }

    // ตรวจสอบสถานะ (หน้า user)
    public function status(Request $request)
    {
        $issues = Issue::orderBy('created_at', 'desc')->get();

        // ถ้าเป็น Ajax หรือ JSON request ให้ส่ง JSON
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'issues' => $issues
            ]);
        }

        // โหลดหน้า Blade ปกติ
        return view('issues.status', compact('issues'));
    }

    // ส่ง Telegram notification
    private function sendTelegramNotification($issue)
    {
        $token = '7769357209:AAFSygZvoFv-ekX4w_LxvTV90S7X9UZ7iaE';
        $chatId = '-4887809695';

        $message = "🛠️ <b>มีการแจ้งปัญหาใหม่!</b>\n"
            . "🏢 อาคาร: {$issue->building}\n"
            . "🚪 ห้อง: {$issue->room}\n"
            . "📦 ครุภัณฑ์: {$issue->equipment}\n"
            . "📧 อีเมล: {$issue->email}\n"
            . "📝 รายละเอียด: " . ($issue->description ?: '-') . "\n"
            . "⏰ เวลา: " . $issue->created_at->format('d/m/Y H:i');

        if ($issue->image_path && file_exists(storage_path('app/public/' . $issue->image_path))) {
            Http::attach(
                'photo',
                file_get_contents(storage_path('app/public/' . $issue->image_path)),
                basename($issue->image_path)
            )->post("https://api.telegram.org/bot{$token}/sendPhoto", [
                'chat_id' => $chatId,
                'caption' => $message,
                'parse_mode' => 'HTML',
            ]);
        } else {
            Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'HTML',
            ]);
        }
    }
}
