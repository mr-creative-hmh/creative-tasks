<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>تقرير أداء المهام الميدانية والمؤسسية</title>
    <style>
        body {
            font-family: 'DejaVu Sans', 'Arial', sans-serif;
            font-size: 12px;
            color: #1e293b;
            line-height: 1.5;
            margin: 0;
            padding: 20px;
        }
        .header {
            border-bottom: 2px solid #0d9488;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .header table {
            width: 100%;
        }
        .title {
            font-size: 18px;
            font-weight: bold;
            color: #0f766e;
        }
        .subtitle {
            font-size: 11px;
            color: #64748b;
        }
        .meta-box {
            background-color: #f0fdfa;
            border: 1px solid #99f6e4;
            border-radius: 6px;
            padding: 10px;
            margin-bottom: 20px;
        }
        .stats-grid {
            width: 100%;
            margin-bottom: 20px;
        }
        .stat-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 10px;
            text-align: center;
            border-radius: 4px;
        }
        .stat-value {
            font-size: 16px;
            font-weight: bold;
            color: #0d9488;
        }
        .stat-label {
            font-size: 10px;
            color: #64748b;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #cbd5e1;
            padding: 8px;
            text-align: right;
        }
        table.data-table th {
            background-color: #0f766e;
            color: #ffffff;
            font-weight: bold;
        }
        table.data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: bold;
        }
        .badge-completed { background-color: #dcfce7; color: #15803d; }
        .badge-progress { background-color: #fef3c7; color: #b45309; }
        .badge-pending { background-color: #fee2e2; color: #b91c1c; }
        .footer {
            margin-top: 30px;
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
            font-size: 10px;
            color: #94a3b8;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <table>
            <tr>
                <td>
                    <div class="title">نظام إدارة المهام والمتابعة الميدانية</div>
                    <div class="subtitle">تقرير أداء ومتابعة إنجاز المهام الدورية</div>
                </td>
                <td style="text-align: left;">
                    <div style="font-size: 11px; color: #64748b;">تاريخ التوليد: {{ $generatedAt }}</div>
                    <div style="font-size: 11px; color: #64748b;">المستخدم: {{ $generatedBy }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="meta-box">
        <table style="width: 100%;">
            <tr>
                <td><strong>الفترة الزمنية:</strong> من {{ $dateFrom }} إلى {{ $dateTo }}</td>
                <td><strong>القسم:</strong> {{ $department ? $department->name : 'جميع الأقسام' }}</td>
                <td><strong>الموظف:</strong> {{ $employee ? $employee->name : 'كافة الموظفين' }}</td>
            </tr>
        </table>
    </div>

    <table class="stats-grid">
        <tr>
            <td style="width: 33%; padding: 4px;">
                <div class="stat-card">
                    <div class="stat-value">{{ $totalTasks }}</div>
                    <div class="stat-label">إجمالي المهام</div>
                </div>
            </td>
            <td style="width: 33%; padding: 4px;">
                <div class="stat-card">
                    <div class="stat-value">{{ $completedTasks }}</div>
                    <div class="stat-label">المهام المكتملة</div>
                </div>
            </td>
            <td style="width: 33%; padding: 4px;">
                <div class="stat-card">
                    <div class="stat-value">{{ $avgProgress }}%</div>
                    <div class="stat-label">متوسط نسبة الإنجاز</div>
                </div>
            </td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 25%;">عنوان المهمة</th>
                <th style="width: 15%;">الموظف</th>
                <th style="width: 15%;">القسم</th>
                <th style="width: 12%;">النوع</th>
                <th style="width: 10%;">نسبة الإنجاز</th>
                <th style="width: 10%;">الحالة</th>
                <th style="width: 8%;">التاريخ</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tasks as $index => $task)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $task->title }}</strong>
                        @if($task->description)
                            <div style="font-size: 10px; color: #64748b;">{{ $task->description }}</div>
                        @endif
                    </td>
                    <td>{{ $task->user?->name }}</td>
                    <td>{{ $task->department?->name }}</td>
                    <td>{{ $task->task_type === 'assigned' ? 'موكلة' : 'عمل ذاتي' }}</td>
                    <td style="text-align: center; font-weight: bold;">{{ $task->progress }}%</td>
                    <td style="text-align: center;">
                        @if($task->status === 'completed')
                            <span class="badge badge-completed">مكتملة</span>
                        @elseif($task->status === 'in_progress')
                            <span class="badge badge-progress">قيد التنفيذ</span>
                        @else
                            <span class="badge badge-pending">معلقة</span>
                        @endif
                    </td>
                    <td style="font-size: 10px;">{{ $task->task_date ? $task->task_date->format('Y-m-d') : '' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align: center; padding: 15px; color: #64748b;">
                        لا توجد سجلات مهام متطابقة مع معايير البحث المحددة.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        تم إنشاء هذا التقرير آلياً بواسطة نظام Creative Tasks • جميع الحقوق محفوظة
    </div>
</body>
</html>
