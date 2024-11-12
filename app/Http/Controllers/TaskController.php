<?php

namespace App\Http\Controllers;
use App\Models\Task;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskStatusRequest;
use App\Http\Requests\ReassignTaskRequest;
use App\Http\Requests\AddCommentRequest;
use App\Http\Requests\AddAttachmentRequest;
use App\Services\TaskService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Exception;

class TaskController extends Controller
{
    protected $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;

        // Middleware للتحكم في الأدوار
        $this->middleware('role:Admin|Manager')->only(['store', 'reassign', 'assign']);
        $this->middleware('role:Admin|Manager|Developer')->only(['updateStatus', 'addAttachment']);
        $this->middleware('role:Admin|Manager|Tester')->only(['addComment', 'blockedTasks']);
        $this->middleware('role:Admin|Manager|Developer|Tester')->only(['show', 'index', 'dailyTasksReport']);
    }

    // إنشاء مهمة جديدة
    public function store(StoreTaskRequest $request)
    {
        try {
            $task = $this->taskService->createTask($request->validated());
            Cache::forget('tasks'); // مسح الكاش عند إضافة مهمة جديدة
            return response()->json(['status' => 'success', 'task' => $task], Response::HTTP_CREATED);
        } catch (Exception $e) {
            Log::error('Error creating task: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Task creation failed'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // تحديث حالة المهمة
    public function updateStatus(UpdateTaskStatusRequest $request, $id)
    {
        try {
            $task = $this->taskService->updateStatus($id, $request->status);
            Cache::forget("task_$id");
            Cache::forget('tasks'); 
            return response()->json(['status' => 'success', 'task' => $task], Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error('Error updating task status: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Status update failed'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // عرض قائمة المهام مع الكاش
    public function index(Request $request)
    {
        try {
            $cacheKey = 'tasks_' . md5(json_encode($request->all()));

            // استرجاع المهام من الكاش إذا كانت موجودة، أو احصل عليها من الخدمة وخزنها
            $tasks = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($request) {
                return $this->taskService->filterTasks($request->all());
            });

            return response()->json(['status' => 'success', 'tasks' => $tasks], Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error('Error retrieving tasks: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Failed to retrieve tasks'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // عرض تفاصيل المهمة مع الكاش
    public function show($id)
    {
        try {
            $task = Cache::remember("task_$id", now()->addMinutes(10), function () use ($id) {
                return $this->taskService->getTaskDetails($id);
            });

            if (!$task) {
                return response()->json(['status' => 'error', 'message' => 'Task not found'], Response::HTTP_NOT_FOUND);
            }

            return response()->json(['status' => 'success', 'task' => $task], Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error('Error retrieving task details: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Task not found'], Response::HTTP_NOT_FOUND);
        }
    }

    // إعادة تعيين مهمة
    public function reassign(ReassignTaskRequest $request, $id)
    {
        try {
            $task = $this->taskService->reassignTask($id, $request->assigned_to);
            Cache::forget("task_$id");
            Cache::forget('tasks'); // مسح الكاش عند إعادة تعيين المهمة
            return response()->json(['status' => 'success', 'task' => $task], Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error('Error reassigning task: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Task reassignment failed'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // إضافة تعليق على مهمة
    public function addComment(AddCommentRequest $request, $id)
    {
        try {
            $comments = $this->taskService->addComment($id, $request->validated());
            Cache::forget("task_$id");
            return response()->json(['status' => 'success', 'comments' => $comments], Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error('Error adding comment: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Failed to add comment'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // إضافة مرفق إلى مهمة
    public function addAttachment(AddAttachmentRequest $request, $id)
    {
        try {
            $filePath = $this->taskService->addAttachment($id, $request->file('attachment'));
            Cache::forget("task_$id");
            return response()->json(['status' => 'success', 'file_path' => $filePath], Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error('Error adding attachment: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Failed to add attachment'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // عرض تقرير المهام اليومية
    public function dailyTasksReport(Request $request)
    {
        try {
            $tasksReport = $this->taskService->filterTasks($request->all());
            return response()->json(['status' => 'success', 'tasks_report' => $tasksReport], Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error('Error generating daily tasks report: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Failed to generate tasks report'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // المهام المحجوزة (أو المهام التي تم إيقافها)
    public function blockedTasks()
    {
        try {
            $blockedTasks = Task::where('status', 'blocked')->get();
            return response()->json(['status' => 'success', 'blocked_tasks' => $blockedTasks], Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error('Error retrieving blocked tasks: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Failed to retrieve blocked tasks'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
