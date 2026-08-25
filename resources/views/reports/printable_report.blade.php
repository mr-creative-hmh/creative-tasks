<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>جامعة المأمون - تقرير الأداء ومتابعة المهام المعتمد</title>
    
    <!-- Google Fonts for Ultra-Crisp Arabic Typography -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&family=Tajawal:wght@400;500;700;900&display=swap" rel="stylesheet">

    <style>
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        body {
            font-family: 'Tajawal', 'Cairo', sans-serif;
            background-color: #f1f5f9;
            color: #0f172a;
            margin: 0;
            padding: 20px;
            direction: rtl;
        }

        /* Screen Wrapper */
        .report-page {
            max-width: 900px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08);
            padding: 35px 40px;
            border: 1px solid #e2e8f0;
        }

        /* Top Action Bar for Screen Only */
        .action-bar {
            max-width: 900px;
            margin: 0 auto 15px auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 20px;
            background: #ffffff;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 18px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 13px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
            border: none;
        }
        .btn-primary {
            background-color: #0284c7;
            color: #ffffff;
        }
        .btn-primary:hover {
            background-color: #0369a1;
        }
        .btn-secondary {
            background-color: #f1f5f9;
            color: #334155;
        }
        .btn-secondary:hover {
            background-color: #e2e8f0;
        }

        /* Official University Header */
        .header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            border-bottom: 3px double #0284c7;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .header-title {
            font-size: 20px;
            font-weight: 900;
            color: #0369a1;
            margin: 3px 0;
        }
        .header-sub {
            font-size: 12px;
            color: #64748b;
            font-weight: 500;
        }
        .header-meta {
            text-align: left;
            font-size: 11px;
            color: #64748b;
            line-height: 1.6;
        }

        /* Meta Box */
        .meta-strip {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-right: 4px solid #0284c7;
            border-radius: 8px;
            padding: 10px 16px;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            font-size: 12px;
            margin-bottom: 20px;
        }

        /* KPI Cards Grid */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 10px;
            margin-bottom: 25px;
        }
        .kpi-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 12px 8px;
            text-align: center;
        }
        .kpi-val {
            font-size: 22px;
            font-weight: 900;
            color: #0f172a;
            font-family: 'Cairo', sans-serif;
        }
        .kpi-lbl {
            font-size: 11px;
            color: #64748b;
            font-weight: 600;
            margin-top: 2px;
        }

        /* Section Headings */
        .section-heading {
            font-size: 14px;
            font-weight: 800;
            color: #1e293b;
            border-bottom: 1.5px solid #e2e8f0;
            padding-bottom: 6px;
            margin-top: 20px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* Table */
        table.report-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 11.5px;
        }
        table.report-table th {
            background-color: #0f172a;
            color: #ffffff;
            font-weight: 700;
            padding: 10px 12px;
            text-align: right;
            border: 1px solid #0f172a;
        }
        table.report-table td {
            padding: 9px 12px;
            border: 1px solid #e2e8f0;
            text-align: right;
            vertical-align: middle;
        }
        table.report-table tr:nth-child(even) {
            background-color: #f8fafc;
        }

        /* Status & Type Badges */
        .badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 10.5px;
            font-weight: 700;
            white-space: nowrap;
        }
        .badge-completed { background-color: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
        .badge-progress { background-color: #fef3c7; color: #b45309; border: 1px solid #fde68a; }
        .badge-pending { background-color: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }
        .badge-type { background-color: #e0f2fe; color: #0369a1; }

        /* Signatures Grid */
        .sig-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-top: 40px;
            page-break-inside: avoid;
        }
        .sig-box {
            text-align: center;
            padding: 10px;
        }
        .sig-line {
            border-top: 1px dashed #94a3b8;
            width: 150px;
            margin: 40px auto 6px auto;
        }

        /* Footer */
        .footer {
            margin-top: 25px;
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
            font-size: 10.5px;
            color: #94a3b8;
            text-align: center;
        }

        /* Print Settings */
        @media print {
            body {
                background-color: #ffffff;
                padding: 0;
            }
            .action-bar {
                display: none !important;
            }
            .report-page {
                border: none;
                box-shadow: none;
                padding: 10mm 12mm;
                max-width: 100%;
            }
            @page {
                size: A4 portrait;
                margin: 8mm 10mm;
            }
        }
    </style>
</head>
<body>

    <!-- SCREEN TOP BAR -->
    <div class="action-bar">
        <div style="font-weight: 700; font-size: 14px; color: #0f172a; display: flex; align-items: center; gap: 8px;">
            <span>📄 معاينة التقرير الرسمي لجامعة المأمون</span>
        </div>
        <div style="display: flex; gap: 10px;">
            <button onclick="window.print()" class="btn btn-primary">
                🖨️ طباعة / حفظ كـ PDF (Save as PDF)
            </button>
            <a href="/reports" class="btn btn-secondary">
                ↩️ العودة للتقارير
            </a>
        </div>
    </div>

    <!-- MAIN REPORT PAGE CONTAINER -->
    <div class="report-page">
        
        <!-- 1. OFFICIAL UNIVERSITY HEADER -->
        <div class="header">
            <div>
                <div style="font-size: 11px; font-weight: 700; color: #475569;">جمهورية العراق • وزارة التعليم العالي والبحث العلمي</div>
                <h1 class="header-title">جامعة المأمون (Al-Ma'moon University)</h1>
                <div class="header-sub">شعبة المتابعة والتنسيق الإداري والميداني • بغداد، العراق</div>
            </div>
            <div class="header-meta">
                <div>رقم التقرير: <strong>REP-{{ date('Ymd') }}-{{ rand(100, 999) }}</strong></div>
                <div>تاريخ السحب: <strong>{{ $generatedAt }}</strong></div>
                <div>المشرف المسؤول: <strong>{{ $generatedBy }}</strong></div>
                <div style="color: #0369a1; font-weight: bold;">almamonuc.edu.iq</div>
            </div>
        </div>

        <!-- 2. METADATA STRIP -->
        <div class="meta-strip">
            <div><strong>الفترة الزمنية:</strong> {{ $dateFrom }} إلى {{ $dateTo }}</div>
            <div><strong>الكلية / القسم:</strong> {{ $department ? $department->name : 'كافة الكليات والأقسام' }}</div>
            <div><strong>الموظف المكلف:</strong> {{ $employee ? $employee->name : 'كافة الكوادر' }}</div>
        </div>

        <!-- 3. SUMMARY KPI CARDS -->
        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-val">{{ $totalTasks }}</div>
                <div class="kpi-lbl">إجمالي المهام</div>
            </div>
            <div class="kpi-card" style="background-color: #f0fdf4; border-color: #86efac;">
                <div class="kpi-val" style="color: #16a34a;">{{ $completedTasks }}</div>
                <div class="kpi-lbl">مكتملة (100%)</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-val" style="color: #d97706;">{{ $inProgressTasks }}</div>
                <div class="kpi-lbl">قيد التنفيذ</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-val" style="color: #64748b;">{{ $pendingTasks }}</div>
                <div class="kpi-lbl">معلقة</div>
            </div>
            <div class="kpi-card" style="background-color: #f0f9ff; border-color: #bae6fd;">
                <div class="kpi-val" style="color: #0284c7;">{{ $avgProgress }}%</div>
                <div class="kpi-lbl">متوسط الإنجاز</div>
            </div>
        </div>

        <!-- 4. DEPARTMENT SUMMARY -->
        @if(count($departmentSummary) > 0)
        <div class="section-heading">
            <span>🏢 أولاً: ملخص أداء الكليات والأقسام</span>
        </div>
        <table class="report-table">
            <thead>
                <tr>
                    <th style="width: 40%;">الكلية / القسم</th>
                    <th style="width: 20%; text-align: center;">إجمالي المهام</th>
                    <th style="width: 20%; text-align: center;">المهام المنجزة</th>
                    <th style="width: 20%; text-align: center;">نسبة الإنجاز المؤسسي</th>
                </tr>
            </thead>
            <tbody>
                @foreach($departmentSummary as $dept)
                <tr>
                    <td><strong>{{ $dept['name'] }}</strong></td>
                    <td style="text-align: center; font-weight: bold;">{{ $dept['total'] }}</td>
                    <td style="text-align: center; color: #16a34a; font-weight: bold;">{{ $dept['completed'] }}</td>
                    <td style="text-align: center; font-weight: bold; color: #0284c7;">{{ $dept['avg_progress'] }}%</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        <!-- 5. DETAILED TASKS LOG -->
        <div class="section-heading">
            <span>📋 ثانياً: السجل التفصيلي للمهام والتكليفات المنجزة</span>
        </div>
        <table class="report-table">
            <thead>
                <tr>
                    <th style="width: 4%;">#</th>
                    <th style="width: 32%;">عنوان المهمة / التوجيه الإداري</th>
                    <th style="width: 16%;">الموظف المكلف</th>
                    <th style="width: 16%;">الكلية / القسم</th>
                    <th style="width: 10%; text-align: center;">النوع</th>
                    <th style="width: 10%; text-align: center;">الإنجاز</th>
                    <th style="width: 12%; text-align: center;">الحالة</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tasks as $index => $task)
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $task->title }}</strong>
                        @if($task->description)
                            <div style="font-size: 10px; color: #64748b; margin-top: 2px;">{{ $task->description }}</div>
                        @endif
                    </td>
                    <td>{{ $task->user?->name ?? 'غير محدد' }}</td>
                    <td>{{ $task->department?->name ?? 'بدون قسم' }}</td>
                    <td style="text-align: center;">
                        <span class="badge badge-type">
                            {{ $task->task_type === 'assigned' ? 'تكليف' : 'عمل ذاتي' }}
                        </span>
                    </td>
                    <td style="text-align: center; font-weight: 800; font-family: 'Cairo', sans-serif;">
                        {{ $task->progress }}%
                    </td>
                    <td style="text-align: center;">
                        @if($task->status === 'completed')
                            <span class="badge badge-completed">مكتملة</span>
                        @elseif($task->status === 'in_progress')
                            <span class="badge badge-progress">قيد التنفيذ</span>
                        @else
                            <span class="badge badge-pending">معلقة</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 25px; color: #94a3b8;">
                        لا توجد سجلات مهام متطابقة مع معايير البحث المحددة.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <!-- 6. OFFICIAL SIGNATURES & STAMPS -->
        <div class="sig-grid">
            <div class="sig-box">
                <div style="font-weight: 800; font-size: 12px; color: #334155;">رئيس القسم / عميد الكلية</div>
                <div style="font-size: 10px; color: #64748b;">(التوقيع والمصادقة)</div>
                <div class="sig-line"></div>
            </div>
            <div class="sig-box">
                <div style="font-weight: 800; font-size: 12px; color: #334155;">ختم الكلية الرسمي</div>
                <div style="width: 75px; height: 75px; border: 1.5px dashed #94a3b8; border-radius: 50%; margin: 8px auto 0 auto; line-height: 75px; font-size: 9px; color: #cbd5e1;">ختم الاعتماد</div>
            </div>
            <div class="sig-box">
                <div style="font-weight: 800; font-size: 12px; color: #334155;">رئاسة جامعة المأمون</div>
                <div style="font-size: 10px; color: #64748b;">شعبة المتابعة والجودة</div>
                <div class="sig-line"></div>
            </div>
        </div>

        <!-- 7. FOOTER -->
        <div class="footer">
            وثيقة إدارية رسمية صادرة آلياً عن منظومة جامعة المأمون الإلكترونية • بغداد، جمهورية العراق • جميع الحقوق محفوظة © {{ date('Y') }}
        </div>

    </div>

</body>
</html>
