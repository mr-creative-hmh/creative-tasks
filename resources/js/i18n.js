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
    
    // GPS & Shift Gating
    locationGateTitle: 'التحقق الجغرافي وساعات الدوام الجامعي',
    locationRequiredMsg: 'يتطلب النظام التحقق من تواجدك داخل الحرم الجامعي (GPS) لفتح لوحة المهام وتسجيل الحضور الميداني.',
    locationRequestBtn: 'تأكيد التواجد بالحرم الجامعي وتسجيل الحضور',
    locationAccessGranted: 'تم تأكيد التواجد بالحرم الجامعي بنجاح',
    locationDenied: 'تم رفض إذن تحديد الموقع الجغرافي. يرجى تفعيل الـ GPS للمتابعة.',
    shiftHours: 'ساعات الدوام الرسمي للقسم',
    outsideShiftWarning: 'تنبيه: أنت حالياً خارج ساعات الدوام الرسمي المعتمدة في كليتك/قسمك',
    insideShiftMsg: 'أنت ضمن ساعات الدوام الرسمي',
    
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
    languagePref: 'لغة الواجهة',
    
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
    
    // Dashboard & Stats
    totalTasks: 'إجمالي المهام والتكليفات',
    completedTasks: 'المهام المكتملة',
    inProgressTasks: 'قيد المتابعة والتنفيذ',
    pendingTasks: 'مهام معلقة',
    avgRate: 'متوسط نسبة الإنجاز الجامعي',
    attendanceToday: 'الكوادر الحاضرة ميدانياً',
    recentActivity: 'آخر تحديثات المهام والأنشطة',
    recentLocations: 'آخر تسجيلات التواجد الميداني',
    
    // Task Form & Table
    assignNewTask: 'تكليف موظف/تدريسي بمهمة جديدة',
    taskTitle: 'عنوان المهمة / التكليف',
    taskDesc: 'التفاصيل والتوجيهات الإدارية',
    assignTo: 'الموظف / التدريسي المكلف',
    taskType: 'نوع المهمة',
    assignedType: 'تكليف من رئاسة القسم/العمادة',
    selfType: 'عمل منجز ذاتياً',
    taskDate: 'تاريخ المهمة',
    assignedBy: 'جهة التكليف / المشرف',
    
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
    role: 'Role',
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
    
    // GPS & Shift Gating
    locationGateTitle: 'Campus Geolocation & Shift Verification',
    locationRequiredMsg: 'GPS verification is required to verify your presence on campus and unlock daily tasks.',
    locationRequestBtn: 'Confirm Campus Location & Check In',
    locationAccessGranted: 'Campus Location Verified Successfully',
    locationDenied: 'Location access was denied. Please enable GPS permissions to proceed.',
    shiftHours: 'Official Working Hours',
    outsideShiftWarning: 'Alert: You are currently outside your official faculty/department hours',
    insideShiftMsg: 'Within official working hours',
    
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
    languagePref: 'Interface Language',
    
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
    
    // Dashboard & Stats
    totalTasks: 'Total Delegations',
    completedTasks: 'Completed Tasks',
    inProgressTasks: 'In Progress',
    pendingTasks: 'Pending Tasks',
    avgRate: 'University Avg Completion',
    attendanceToday: 'Staff on Campus Today',
    recentActivity: 'Recent Task Activity',
    recentLocations: 'Recent Live Check-ins',
    
    // Task Form & Table
    assignNewTask: 'Assign Task to Staff',
    taskTitle: 'Task Title',
    taskDesc: 'Administrative Directives / Notes',
    assignTo: 'Assigned Staff',
    taskType: 'Task Type',
    assignedType: 'Assigned by Dean/Head',
    selfType: 'Self-Reported',
    taskDate: 'Task Date',
    assignedBy: 'Assigned By',
    
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

export function t(key) {
  const lang = i18nState.locale;
  return translations[lang]?.[key] || translations['ar']?.[key] || key;
}

export function setLocale(lang) {
  if (['ar', 'en'].includes(lang)) {
    i18nState.locale = lang;
    localStorage.setItem('app_locale', lang);
    document.documentElement.dir = lang === 'ar' ? 'rtl' : 'ltr';
    document.documentElement.lang = lang;
  }
}
