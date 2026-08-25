import { reactive, computed } from 'vue';

export const i18nState = reactive({
  locale: localStorage.getItem('app_locale') || 'ar'
});

export const currentLocale = computed({
  get: () => i18nState.locale,
  set: (val) => {
    setLocale(val);
  }
});

export const translations = {
  ar: {
    // App Brand & General
    appName: 'جامعة المأمون',
    appSubtitle: 'نظام إدارة ومتابعة المهام الميدانية والأكاديمية',
    loginTitle: 'بوابة تسجيل الدخول - جامعة المأمون',
    loginSubtitle: 'يرجى إدخال بيانات حسابك الجامعي للمتابعة والتحقق الميداني',
    email: 'البريد الإلكتروني الجامعي',
    password: 'كلمة المرور',
    rememberMe: 'تذكر تسجيل الدخول',
    signIn: 'دخول النظام',
    signingIn: 'جاري التحقق...',
    logout: 'تسجيل الخروج',
    role: 'الصفة / الدور الوظيفي',
    department: 'الكلية / القسم / الشعبة',
    status: 'الحالة',
    actions: 'الإجراءات',
    save: 'حفظ التعديلات',
    cancel: 'إلغاء',
    edit: 'تعديل',
    delete: 'حذف',
    add: 'إضافة',
    confirm: 'تأكيد',
    refresh: 'تحديث',
    filter: 'تصفية',
    search: 'بحث بالاسم أو البريد الإلكتروني...',
    all: 'الكل',
    active: 'نشط',
    inactive: 'معطل',
    today: 'اليوم',
    thisWeek: 'هذا الأسبوع',
    thisMonth: 'هذا الشهر',
    dateFrom: 'من تاريخ',
    dateTo: 'إلى تاريخ',
    exportPdf: 'سحب تقرير أداء رسمي (PDF)',
    preferences: 'التفضيلات',
    googleMaps: 'Google Maps',
    
    // Statuses & Types (Strictly without awkward line-breaking)
    statusCompleted: 'مكتملة',
    statusInProgress: 'قيد التنفيذ',
    statusPending: 'معلقة',
    typeAssigned: 'موكلة',
    typeSelf: 'ذاتية',

    // GPS & Shift Gating
    locationGateTitle: 'التحقق الجغرافي وساعات الدوام الجامعي',
    locationRequiredMsg: 'يتطلب النظام التحقق من تواجدك داخل الحرم الجامعي (GPS) لفتح لوحة المهام وتسجيل الحضور الميداني.',
    locationRequestBtn: 'تأكيد التواجد بالحرم الجامعي وتسجيل الحضور',
    locationAccessGranted: 'تم تأكيد التواجد بالحرم الجامعي بنجاح',
    locationDenied: 'تم رفض إذن تحديد الموقع الجغرافي. يرجى تفعيل الـ GPS للمتابعة.',
    shiftHours: 'ساعات الدوام الرسمي للقسم',
    outsideShiftWarning: 'تنبيه: أنت حالياً خارج ساعات الدوام الرسمي المعتمدة في كليتك/قسمك',
    insideShiftMsg: 'أنت ضمن ساعات الدوام الرسمي',
    gpsVerified: 'تم تسجيل وتحديث تواجدك بالحرم الجامعي آلياً',
    gpsWaiting: 'بانتظار الـ GPS',
    gpsEnforcedTitle: 'التحقق الجغرافي إجباري (GPS Enforced)',
    gpsScanningTitle: 'جاري التحقق التلقائي من الموقع...',
    gpsScanningDesc: 'النظام يلتقط إحداثيات تواجدك بالحرم الجامعي لتسجيل الحضور وتفعيل مهامك اليومية.',
    gpsLockDesc: 'يتطلب النظام التحقق التلقائي من موقعك الجغرافي داخل الحرم الجامعي لفتح المهام وتسجيل الحضور الميداني.',
    retryGps: 'إعادة محاولة التحقق من الـ GPS',
    demoCampusBtn: 'تأكيد بموقع الحرم الجامعي (تجربة واختبار)',
    autoTrackingActive: 'التتبع التلقائي نشط',
    
    // Navigation
    navDashboard: 'لوحة القيادة والمؤشرات',
    navTasks: 'إدارة المهام والتكليفات',
    navAttendance: 'خريطة التواجد الميداني',
    navDepartments: 'الكليات والأقسام وساعات الدوام',
    navUsers: 'إدارة المستخدمين والكوادر',
    navReports: 'تقارير الأداء المؤسسي',
    navEmployeePortal: 'مهامي اليومية',
    navProfile: 'الملف الشخصي والإعدادات',
    
    // Users Management
    addUser: 'إضافة مستخدم / كادر جديد',
    userName: 'الاسم الكامل',
    userRole: 'الدور الوظيفي والصلاحية',
    userDepartment: 'الكلية / القسم التابع له',
    userStatus: 'حالة الحساب',
    selectRole: 'اختر الدور الوظيفي',
    selectDept: 'اختر الكلية / القسم (اختياري)',
    optionalPasswordHint: 'اتركه فارغاً للإبقاء على كلمة المرور الحالية',
    toggleActive: 'تبديل حالة النشاط',
    totalUsers: 'إجمالي الكوادر والمستخدمين',
    activeUsers: 'الحسابات النشطة',
    allDepartments: 'كافة الأقسام',
    allEmployees: 'كافة الموظفين',
    allRoles: 'كافة الصلاحيات',
    activeAccountCheckbox: 'حساب نشط ومفعّل لتسجيل الدخول',
    confirmDeleteUser: 'هل أنت متأكد من رغبتك بحذف هذا المستخدم نهائياً؟',
    
    // Profile
    profileTitle: 'الملف الشخصي وبيانات الحساب',
    profileSubtitle: 'إدارة معلومات حسابك الجامعي وكلمة المرور وتفضيلات النظام',
    personalInfo: 'المعلومات الشخصية',
    changePassword: 'تغيير كلمة المرور',
    currentPassword: 'كلمة المرور الحالية',
    newPassword: 'كلمة المرور الجديدة',
    confirmPassword: 'تأكيد كلمة المرور الجديدة',
    systemPreferences: 'تفضيلات العرض والمظهر',
    themeMode: 'وضع العرض (Dark / Light Mode)',
    languagePref: 'لغة الواجهة (العربية / English)',
    todayAttendanceTime: 'تسجيل حضور اليوم:',
    savedSuccess: 'تم الحفظ بنجاح!',
    passwordChangedSuccess: 'تم تحديث كلمة المرور بنجاح!',
    
    // Employee Portal
    employeeTitle: 'بوابة المهام الميدانية والأكاديمية',
    tabAssigned: 'المهام والتكليفات الموكلة إليّ',
    tabSelfReported: 'الأعمال اليومية الذاتية',
    quickAddPlaceholder: 'سجّل إنجازاً أو عملاً ميدانياً تم إنجازه اليوم داخل الجامعة...',
    addQuickTask: 'إضافة إنجاز يومي',
    progress: 'نسبة الإنجاز',
    quickSlidePrompt: 'اسحب لتحديث نسبة الإنجاز فورياً:',
    noAssignedTasks: 'لا توجد تكليفات موكلة إليك لهذا اليوم.',
    noSelfTasks: 'لم تقم بتسجيل أعمال ذاتية اليوم بعد.',
    todayCompletion: 'إجمالي إنجاز المهام اليوم',
    tasksCompletionSummary: 'تم إنجاز {completed} من أصل {total} مهمة',
    quickSelfTitle: 'تسجيل عمل ميداني / ذاتي سريع',
    quickSelfSubtitle: 'أضف ما تم تنفيذه في سطر واحد مباشرة',
    savingProgress: 'جاري الحفظ...',
    
    // Dashboard & Stats
    totalTasks: 'إجمالي المهام والتكليفات',
    completedTasks: 'المهام المكتملة',
    inProgressTasks: 'قيد التنفيذ',
    pendingTasks: 'مهام معلقة',
    avgRate: 'متوسط نسبة الإنجاز الجامعي',
    attendanceToday: 'الكوادر الحاضرة ميدانياً',
    recentActivity: 'آخر تحديثات المهام والأنشطة',
    recentLocations: 'آخر تسجيلات التواجد الميداني',
    
    // Task Management
    assignNewTask: 'تكليف موظف/تدريسي بمهمة جديدة',
    editTask: 'تعديل بيانات المهمة وتحديث الإنجاز',
    taskTitle: 'عنوان المهمة / التكليف',
    taskDesc: 'التفاصيل والتوجيهات الإدارية',
    assignTo: 'الموظف / التدريسي المكلف',
    taskType: 'نوع المهمة',
    assignedType: 'تكليف من رئاسة القسم/العمادة',
    selfType: 'عمل منجز ذاتياً',
    taskDate: 'تاريخ المهمة',
    assignedBy: 'جهة التكليف / المشرف',
    allStatuses: 'كافة الحالات',
    allTypes: 'كافة الأنواع',
    noTasksFound: 'لا توجد مهام متطابقة مع شروط التصفية الحالية.',
    confirmDeleteTask: 'هل أنت متأكد من حذف هذه المهمة؟',
    selectEmployeePlaceholder: 'اختر الموظف...',
    taskTitlePlaceholder: 'أدخل عنوان المهمة بوضوح...',
    taskDescPlaceholder: 'الملاحظات والتوجيهات الميدانية...',

    // Attendance & Map
    attendanceMapTitle: 'خريطة التتبع الميداني الحي وإدارة إحداثيات ومواقع حضور الموظفين بالـ GPS',
    adminManualLocationTitle: 'أداة تعديل موقع الموظف يدوياً عبر الخريطة (خاص بالإدارة):',
    targetEmployee: 'الموظف المراد تعديل موقعه',
    latitude: 'خط العرض (Latitude)',
    longitude: 'خط الطول (Longitude)',
    pinLocationBtn: 'تثبيت الموقع الميداني',
    savingManualLocation: 'جاري الحفظ...',
    attendanceTableTitle: 'سجل الحضور والإحداثيات',
    totalRecords: 'إجمالي السجلات:',
    tableEmployee: 'الموظف',
    tableDepartment: 'القسم',
    tableCoordinates: 'إحداثيات الـ GPS',
    tableLogTime: 'وقت التسجيل',
    tableLogDate: 'التاريخ',
    tableMapAction: 'عرض بالخريطة',
    mapHintAdmin: '💡 انقر فوق أي موقع في الخريطة لتحديد وتعديل إحداثيات الموظف يدوياً',
    
    // Department Management
    addDepartment: 'إضافة كلية / قسم / شعبة',
    deptName: 'اسم الكلية / القسم / الشعبة',
    deptManager: 'رئيس القسم / العميد',
    selectManager: 'اختر رئيس القسم (اختياري)',
    workStartTime: 'بداية الدوام',
    workEndTime: 'نهاية الدوام',
    employeeCount: 'عدد الكادر',
    
    // Roles
    adminRole: 'رئاسة الجامعة / العمادة (Super Admin)',
    headRole: 'رئيس قسم / عميد كلية (Head)',
    employeeRole: 'كادر إداري / ميداني (Staff)',
  },
  en: {
    // App Brand & General
    appName: "Al-Ma'moon University",
    appSubtitle: 'Academic & Field Task Management System',
    loginTitle: "Al-Ma'moon University Portal",
    loginSubtitle: 'Please sign in with your university account credentials',
    email: 'University Email',
    password: 'Password',
    rememberMe: 'Remember Me',
    signIn: 'Sign In',
    signingIn: 'Authenticating...',
    logout: 'Sign Out',
    role: 'Role & Authority',
    department: 'Faculty / Department',
    status: 'Status',
    actions: 'Actions',
    save: 'Save Changes',
    cancel: 'Cancel',
    edit: 'Edit',
    delete: 'Delete',
    add: 'Add',
    confirm: 'Confirm',
    refresh: 'Refresh',
    filter: 'Filter',
    search: 'Search by name or email...',
    all: 'All',
    active: 'Active',
    inactive: 'Inactive',
    today: 'Today',
    thisWeek: 'This Week',
    thisMonth: 'This Month',
    dateFrom: 'Date From',
    dateTo: 'Date To',
    exportPdf: 'Export Performance PDF',
    preferences: 'Preferences',
    googleMaps: 'Google Maps',

    // Statuses & Types
    statusCompleted: 'Completed',
    statusInProgress: 'In Progress',
    statusPending: 'Pending',
    typeAssigned: 'Assigned',
    typeSelf: 'Self-Reported',
    
    // GPS & Shift Gating
    locationGateTitle: 'Campus Geolocation & Shift Verification',
    locationRequiredMsg: 'GPS verification is required to verify your presence on campus and unlock daily tasks.',
    locationRequestBtn: 'Confirm Campus Location & Check In',
    locationAccessGranted: 'Campus Location Verified Successfully',
    locationDenied: 'Location access was denied. Please enable GPS permissions to proceed.',
    shiftHours: 'Official Working Hours',
    outsideShiftWarning: 'Alert: You are currently outside your official faculty/department hours',
    insideShiftMsg: 'Within official working hours',
    gpsVerified: 'Campus attendance verified automatically',
    gpsWaiting: 'Waiting for GPS',
    gpsEnforcedTitle: 'GPS Access Required (GPS Enforced)',
    gpsScanningTitle: 'Scanning campus location automatically...',
    gpsScanningDesc: 'The system is detecting your campus location coordinates to verify attendance and unlock tasks.',
    gpsLockDesc: 'Automatic GPS presence verification on campus is strictly mandatory to unlock tasks.',
    retryGps: 'Retry GPS Verification',
    demoCampusBtn: 'Confirm Campus Location (Simulation / Test)',
    autoTrackingActive: 'Auto-tracking active',
    
    // Navigation
    navDashboard: 'Dashboard & KPIs',
    navTasks: 'Tasks & Delegations',
    navAttendance: 'Attendance Map',
    navDepartments: 'Faculties & Shifts',
    navUsers: 'Users & Staff',
    navReports: 'Performance Reports',
    navEmployeePortal: 'My Daily Tasks',
    navProfile: 'Profile & Settings',
    
    // Users Management
    addUser: 'Add New User / Staff',
    userName: 'Full Name',
    userRole: 'Role & Permissions',
    userDepartment: 'Assigned Department',
    userStatus: 'Account Status',
    selectRole: 'Select Role',
    selectDept: 'Select Department (Optional)',
    optionalPasswordHint: 'Leave blank to keep current password',
    toggleActive: 'Toggle Active Status',
    totalUsers: 'Total Users',
    activeUsers: 'Active Accounts',
    allDepartments: 'All Departments',
    allEmployees: 'All Staff',
    allRoles: 'All Roles',
    activeAccountCheckbox: 'Active account authorized for login',
    confirmDeleteUser: 'Are you sure you want to permanently delete this user?',
    
    // Profile
    profileTitle: 'My Profile & Account Settings',
    profileSubtitle: 'Manage your university profile, password, and theme preferences',
    personalInfo: 'Personal Information',
    changePassword: 'Change Password',
    currentPassword: 'Current Password',
    newPassword: 'New Password',
    confirmPassword: 'Confirm New Password',
    systemPreferences: 'System Preferences',
    themeMode: 'Theme (Dark / Light Mode)',
    languagePref: 'Interface Language (Arabic / English)',
    todayAttendanceTime: 'Today Attendance Time:',
    savedSuccess: 'Changes saved successfully!',
    passwordChangedSuccess: 'Password updated successfully!',
    
    // Employee Portal
    employeeTitle: 'Staff & Academic Tasks Portal',
    tabAssigned: 'Assigned Delegations',
    tabSelfReported: 'Daily Self-Reported Work',
    quickAddPlaceholder: 'Log a completed campus or field task in one line...',
    addQuickTask: 'Add Completed Task',
    progress: 'Progress',
    quickSlidePrompt: 'Slide to update progress instantly:',
    noAssignedTasks: 'No assigned delegations found for today.',
    noSelfTasks: 'No self-reported work logged today.',
    todayCompletion: 'Today Overall Completion',
    tasksCompletionSummary: '{completed} of {total} tasks completed',
    quickSelfTitle: 'Quick Daily Field Entry',
    quickSelfSubtitle: 'Record completed work directly in one line',
    savingProgress: 'Saving...',
    
    // Dashboard & Stats
    totalTasks: 'Total Delegations',
    completedTasks: 'Completed Tasks',
    inProgressTasks: 'In Progress',
    pendingTasks: 'Pending Tasks',
    avgRate: 'University Avg Completion',
    attendanceToday: 'Staff on Campus Today',
    recentActivity: 'Recent Task Activity',
    recentLocations: 'Recent Live Check-ins',
    
    // Task Management
    assignNewTask: 'Assign Task to Staff',
    editTask: 'Edit Task & Update Progress',
    taskTitle: 'Task Title',
    taskDesc: 'Administrative Directives / Notes',
    assignTo: 'Assigned Staff',
    taskType: 'Task Type',
    assignedType: 'Assigned by Dean/Head',
    selfType: 'Self-Reported',
    taskDate: 'Task Date',
    assignedBy: 'Assigned By',
    allStatuses: 'All Statuses',
    allTypes: 'All Types',
    noTasksFound: 'No tasks found matching current filters.',
    confirmDeleteTask: 'Are you sure you want to delete this task?',
    selectEmployeePlaceholder: 'Select Staff Member...',
    taskTitlePlaceholder: 'Enter clear task title...',
    taskDescPlaceholder: 'Field directives and instructions...',

    // Attendance & Map
    attendanceMapTitle: 'Live Field Tracking Map & GPS Staff Attendance Management',
    adminManualLocationTitle: 'Manual Staff Location Override via Map (Admin Only):',
    targetEmployee: 'Target Staff Member',
    latitude: 'Latitude',
    longitude: 'Longitude',
    pinLocationBtn: 'Set & Pin Location',
    savingManualLocation: 'Saving...',
    attendanceTableTitle: 'Attendance & Coordinates Log',
    totalRecords: 'Total Records:',
    tableEmployee: 'Staff Name',
    tableDepartment: 'Department',
    tableCoordinates: 'GPS Coordinates',
    tableLogTime: 'Check-in Time',
    tableLogDate: 'Date',
    tableMapAction: 'View on Map',
    mapHintAdmin: '💡 Click anywhere on the map to manually pick and update staff coordinates',
    
    // Department Management
    addDepartment: 'Add Faculty / Department',
    deptName: 'Faculty / Department Name',
    deptManager: 'Dean / Head of Department',
    selectManager: 'Select Head (Optional)',
    workStartTime: 'Shift Start Time',
    workEndTime: 'Shift End Time',
    employeeCount: 'Staff Count',
    
    // Roles
    adminRole: 'University Presidency (Super Admin)',
    headRole: 'Faculty Dean / Dept Head',
    employeeRole: 'Staff / Faculty Member',
  }
};

export function t(key, params = {}) {
  const lang = i18nState.locale;
  let text = translations[lang]?.[key] || translations['ar']?.[key] || key;
  for (const [k, v] of Object.entries(params)) {
    text = text.replace(`{${k}}`, v);
  }
  return text;
}

export function setLocale(lang) {
  if (['ar', 'en'].includes(lang)) {
    i18nState.locale = lang;
    localStorage.setItem('app_locale', lang);
    document.documentElement.dir = lang === 'ar' ? 'rtl' : 'ltr';
    document.documentElement.lang = lang;
  }
}
