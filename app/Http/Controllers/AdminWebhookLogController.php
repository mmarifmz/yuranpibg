<?php
// app/Http/Controllers/AdminWebhookLogController.php
namespace App\Http\Controllers;

use App\Models\WebhookLog;
use Illuminate\Http\Request;

class AdminWebhookLogController extends Controller
{
    public function index()
    {
        $logs = WebhookLog::latest()->paginate(20);
        return view('admin.webhook_logs.index', compact('logs'));
    }
}