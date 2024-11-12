<?php

namespace App\Services;

use App\Models\Task;
use App\Models\TaskDependency;
use App\Models\ProjectUser;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use Exception;

class TaskService
{
    // إنشاء مهمة جديدة
    public function createTask(array $data)
    {
        try {
            $task = Task::create([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'type' => $data['type'],
                'status' => $data['status'] ?? 'open',
                'priority' => $data['priority'],
                'due_date' => $data['due_date'] ?? null,
                'assigned_to' => $data['assigned_to'] ?? null,
                'created_by' => Auth::id(),
            ]);

            // إضافة تبعيات المهام إذا وجدت
            if (isset($data['dependencies'])) {
                $this->addTaskDependencies($task->id, $data['dependencies']);
                $this->checkAndSetBlockedStatus($task->id);
            }

            return $task;
        } catch (Exception $e) {
            Log::error('Error creating task: ' . $e->getMessage());
            throw new Exception('Task creation failed');
        }
    }

    // إضافة تبعيات المهام
    public function addTaskDependencies($taskId, $dependencies)
    {
        foreach ($dependencies as $dependentTaskId) {
            TaskDependency::create([
                'task_id' => $taskId,
                'dependent_task_id' => $dependentTaskId,
            ]);
        }
    }

    // تحديث حالة المهمة
    public function updateStatus($taskId, $status)
    {
        $task = Task::findOrFail($taskId);
        $task->status = $status;
        $task->save();

        // تحديث حالة المهام المعتمدة
        if ($status === 'completed') {
            $this->updateDependentTasks($taskId);
        }

        return $task;
    }

    // إعادة تعيين مهمة لمستخدم آخر
    public function reassignTask($taskId, $userId)
    {
        $task = Task::findOrFail($taskId);
        $task->assigned_to = $userId;
        $task->save();

        return $task;
    }

    // إضافة تعليق لمهمة
    public function addComment($taskId, array $data)
    {
        $task = Task::findOrFail($taskId);
        $task->comments = $task->comments ? $task->comments . "\n" . $data['content'] : $data['content'];
        $task->save();

        return $task->comments;
    }

    // إضافة مرفق لمهمة
    public function addAttachment($taskId, $file)
    {
        $task = Task::findOrFail($taskId);
        $filePath = $file->store('attachments', 'public');

        $task->attachments = $task->attachments ? $task->attachments . ',' . $filePath : $filePath;
        $task->save();

        return $filePath;
    }

    // تصفية المهام بناءً على عوامل متعددة
    public function filterTasks(array $filters)
    {
        $query = Task::query();

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (isset($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }
        if (isset($filters['assigned_to'])) {
            $query->where('assigned_to', $filters['assigned_to']);
        }
        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }
        if (isset($filters['due_date'])) {
            $query->whereDate('due_date', $filters['due_date']);
        }

        return $query->get();
    }

    // عرض تفاصيل المهمة
    public function getTaskDetails($taskId)
    {
        return Task::findOrFail($taskId);
    }

    // تحقق من المهام المعتمدة لتحديث حالتها إلى Blocked
    private function checkAndSetBlockedStatus($taskId)
    {
        $dependencies = TaskDependency::where('task_id', $taskId)->get();

        foreach ($dependencies as $dependency) {
            $dependentTask = Task::find($dependency->dependent_task_id);
            if ($dependentTask && $dependentTask->status !== 'completed') {
                Task::where('id', $taskId)->update(['status' => 'blocked']);
                return;
            }
        }
    }

    // تحديث المهام المعتمدة عند إكمال المهمة
    private function updateDependentTasks($taskId)
    {
        $dependentTasks = TaskDependency::where('dependent_task_id', $taskId)->get();

        foreach ($dependentTasks as $dependency) {
            $task = Task::find($dependency->task_id);
            if ($task && $this->areDependenciesCompleted($task->id)) {
                $task->status = 'open';
                $task->save();
            }
        }
    }

    // التحقق من إكمال جميع التبعيات لمهمة معينة
    private function areDependenciesCompleted($taskId)
    {
        $dependencies = TaskDependency::where('task_id', $taskId)->get();

        foreach ($dependencies as $dependency) {
            $task = Task::find($dependency->dependent_task_id);
            if ($task && $task->status !== 'completed') {
                return false;
            }
        }
        return true;
    }


}
