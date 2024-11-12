<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTasksTable extends Migration
{
    public function up()
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['Bug', 'Feature', 'Improvement']);
            $table->enum('status', ['Open', 'In Progress', 'Completed', 'Blocked'])->default('Open');
            $table->enum('priority', ['Low', 'Medium', 'High'])->default('Medium');
            $table->date('due_date')->nullable();
            $table->foreignId('assigned_to')->constrained('users')->onDelete('cascade');

            // تعريف الحقل يعتمد عليه
            $table->unsignedBigInteger('depends_on')->nullable();

            // إنشاء العلاقة مع نفس الجدول
            $table->foreign('depends_on')->references('id')->on('tasks')->onDelete('set null');

            $table->softDeletes();

            // الفهارس لتحسين الأداء
            $table->index('status');
            $table->index('assigned_to');
            $table->index('due_date');
            $table->index('priority');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tasks');
    }
}
