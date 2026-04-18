export default {
    // ─── Common ────────────────────────────────────────────────────
    common: {
        appName: 'Cloudy Learning',
        save: 'Lưu',
        cancel: 'Hủy',
        delete: 'Xóa',
        edit: 'Chỉnh sửa',
        create: 'Tạo mới',
        search: 'Tìm kiếm',
        loading: 'Đang tải…',
        submit: 'Gửi',
        confirm: 'Xác nhận',
        close: 'Đóng',
        back: 'Quay lại',
        next: 'Tiếp theo',
        previous: 'Trước',
        viewAll: 'Xem tất cả',
        noData: 'Không có dữ liệu',
        success: 'Thành công',
        error: 'Lỗi',
        today: 'Hôm nay',
        learnMore: 'Tìm hiểu thêm',
        by: 'bởi',
        pageOf: 'Trang {current} / {total}',
    },

    // ─── Navbar (user site) ─────────────────────────────────────────
    nav: {
        courses: 'Khóa học',
        about: 'Giới thiệu',
        contact: 'Liên hệ',
        myLearning: 'Học của tôi',
        signIn: 'Đăng nhập',
        getStarted: 'Bắt đầu',
        signOut: 'Đăng xuất',
        notifications: 'Thông báo',
        markAllRead: 'Đánh dấu đã đọc',
        noNotifications: 'Chưa có thông báo',
    },

    // ─── Auth ───────────────────────────────────────────────────────
    auth: {
        login: {
            title: 'Đăng nhập',
            subtitle: 'Chào mừng trở lại',
            tagline: 'Đăng nhập để học',
            email: 'Địa chỉ email',
            password: 'Mật khẩu',
            rememberMe: 'Ghi nhớ đăng nhập',
            signIn: 'Đăng nhập',
            signingIn: 'Đang đăng nhập…',
            noAccount: 'Chưa có tài khoản?',
            register: 'Đăng ký ngay',
            hero: {
                tagline: 'Hành trình học tập của bạn',
                headline: 'Mở khóa hàng nghìn khóa học từ các chuyên gia thực thụ.',
                body: 'Đăng nhập để truy cập các khóa học đã đăng ký, theo dõi tiến độ và tiếp tục từ nơi bạn đã dừng.',
            },
            stats: {
                courses: { label: 'Khóa học', description: 'Cập nhật liên tục' },
                learners: { label: 'Học viên', description: 'Học mỗi ngày' },
                instructors: { label: 'Giảng viên', description: 'Chuyên gia thực chiến' },
            },
        },
        register: {
            title: 'Đăng ký',
            subtitle: 'Tạo tài khoản',
            name: 'Họ và tên',
            email: 'Địa chỉ email',
            password: 'Mật khẩu',
            passwordConfirm: 'Xác nhận mật khẩu',
            role: 'Bạn là',
            roleStudent: 'Học viên',
            roleInstructor: 'Giảng viên',
            dateOfBirth: 'Ngày sinh',
            sex: 'Giới tính',
            sexMale: 'Nam',
            sexFemale: 'Nữ',
            sexOther: 'Khác',
            sexPrefer: 'Không muốn tiết lộ',
            categories: 'Chủ đề quan tâm (chọn tối đa 3)',
            submit: 'Tạo tài khoản',
            submitting: 'Đang tạo…',
            haveAccount: 'Đã có tài khoản?',
            signIn: 'Đăng nhập',
        },
    },

    // ─── Admin auth ─────────────────────────────────────────────────
    adminAuth: {
        login: {
            subtitle: 'Chào mừng trở lại',
            title: 'Đăng nhập quản trị',
            email: 'Địa chỉ email',
            password: 'Mật khẩu',
            signIn: 'Đăng nhập',
            signingIn: 'Đang đăng nhập…',
            hero: {
                tagline: 'Trung tâm vận hành',
                headline: 'Quản lý khóa học, người dùng và xuất bản từ một không gian làm việc.',
                body: 'Đăng nhập để xem tăng trưởng bài học, kiểm duyệt hoạt động học viên và duy trì nội dung.',
            },
        },
        logout: 'Đăng xuất',
        loggingOut: 'Đang đăng xuất…',
    },

    // ─── Home ───────────────────────────────────────────────────────
    home: {
        hero: {
            headline: 'Học không giới hạn',
            body: 'Khám phá hàng trăm khóa học từ các chuyên gia hàng đầu.',
            cta: 'Khám phá khóa học',
            ctaSecondary: 'Đăng ký miễn phí',
        },
        popularCourses: 'Khóa học phổ biến',
        newestCourses: 'Khóa học mới nhất',
        topInstructors: 'Giảng viên nổi bật',
        instructorsSubtitle: 'Đây là một số giảng viên phổ biến nhất của chúng tôi — những chuyên gia thực chiến mang đến kinh nghiệm thực tế cho mỗi bài học.',
        viewCourses: 'Xem khóa học',
        noRatings: 'Chưa có đánh giá',
        instructorBadge: 'Giảng viên',
    },

    // ─── Courses ────────────────────────────────────────────────────
    courses: {
        title: 'Khóa học',
        searchPlaceholder: 'Tìm kiếm khóa học…',
        filterCategory: 'Danh mục',
        filterInstructor: 'Giảng viên',
        filterTag: 'Thẻ',
        allCategories: 'Tất cả danh mục',
        allInstructors: 'Tất cả giảng viên',
        allTags: 'Tất cả thẻ',
        lessons: 'bài học',
        lesson: 'bài học',
        noResults: 'Không tìm thấy khóa học nào.',
        loading: 'Đang tải khóa học…',
        free: 'Miễn phí',
        filters: 'Bộ lọc',
        loadingCourse: 'Đang tải khóa học...',
        notFound: 'Không tìm thấy khóa học.',
        backToCourses: 'Quay lại khóa học',
        lessonsTitle: 'Bài học ({count})',
        noLessons: 'Chưa có bài học nào.',
    },

    // ─── Dashboard ──────────────────────────────────────────────────
    dashboard: {
        welcomeBack: 'Chào mừng trở lại,',
        subtitle: 'Tiếp tục hành trình học tập của bạn',
        browseAll: 'Xem tất cả khóa học',
        availableCourses: 'Khóa học hiện có',
        yourAccount: 'Tài khoản của bạn',
        memberSince: 'Thành viên từ',
        noCourses: 'Chưa có khóa học. Hãy kiểm tra lại sau!',
        previous: '← Trước',
        nextPage: 'Tiếp →',
        pageOf: 'Trang {current} / {total}',
        today: 'Hôm nay',
    },

    // ─── Profile ────────────────────────────────────────────────────
    profile: {
        title: 'Hồ sơ của tôi',
        personalInfo: 'Thông tin cá nhân',
        name: 'Họ và tên',
        email: 'Email',
        password: 'Mật khẩu mới',
        passwordConfirm: 'Xác nhận mật khẩu',
        dateOfBirth: 'Ngày sinh',
        sex: 'Giới tính',
        bio: 'Giới thiệu bản thân',
        categories: 'Chủ đề quan tâm',
        saveChanges: 'Lưu thay đổi',
        saving: 'Đang lưu…',
        updateSuccess: 'Cập nhật hồ sơ thành công!',
    },

    // ─── About ──────────────────────────────────────────────────────
    about: {
        badge: 'Về chúng tôi',
        headline: 'Học không có giới hạn',
        body: 'Cloudy Learning là nền tảng giáo dục trực tuyến kết nối người học với giảng viên chuyên nghiệp từ khắp nơi trên thế giới.',
        mission: {
            title: 'Sứ mệnh của chúng tôi',
            body: 'Mang giáo dục đẳng cấp thế giới đến với mọi người — không phân biệt địa lý, hoàn cảnh hay tài chính.',
        },
        vision: {
            title: 'Tầm nhìn của chúng tôi',
            body: 'Một thế giới nơi bất kỳ ai cũng có thể học bất cứ điều gì, từ bất kỳ ai.',
        },
        standFor: 'Chúng tôi đại diện cho điều gì',
        valuesSubtitle: 'Các giá trị cốt lõi định hướng mọi điều chúng tôi xây dựng và mọi quyết định chúng tôi đưa ra.',
        stats: [
            { value: '50K+', label: 'Học viên tích cực' },
            { value: '500+', label: 'Khóa học' },
            { value: '80+',  label: 'Giảng viên chuyên gia' },
            { value: '98%',  label: 'Tỉ lệ hài lòng' },
        ],
        values: [
            { icon: '🎯', title: 'Khả năng tiếp cận', desc: 'Giáo dục tốt không có rào cản. Chúng tôi cung cấp học tập miễn phí và giá cả phải chăng cho tất cả mọi người.' },
            { icon: '💡', title: 'Chất lượng', desc: 'Mỗi khóa học được đánh giá cẩn thận để đảm bảo đáp ứng tiêu chuẩn cao nhất về nội dung và truyền đạt.' },
            { icon: '🤝', title: 'Cộng đồng', desc: 'Chúng tôi tin rằng học cùng nhau tốt hơn. Nền tảng thúc đẩy sự hợp tác và hỗ trợ lẫn nhau.' },
            { icon: '🚀', title: 'Đổi mới', desc: 'Chúng tôi liên tục cải thiện nền tảng với công nghệ hiện đại để mang lại trải nghiệm tốt nhất cho học viên.' },
            { icon: '🌍', title: 'Đa dạng', desc: 'Chúng tôi trân trọng giảng viên và học viên từ mọi nền tảng, văn hóa và mọi nơi trên thế giới.' },
            { icon: '🔒', title: 'Niềm tin', desc: 'Chúng tôi xử lý dữ liệu và quyền riêng tư của bạn với sự quan tâm và minh bạch cao nhất.' },
        ],
        cta: {
            headline: 'Sẵn sàng bắt đầu hành trình?',
            body: 'Tham gia cùng hơn 50.000 học viên đang phát triển trên Cloudy Learning.',
            button: 'Bắt đầu miễn phí',
        },
    },

    // ─── Contact ────────────────────────────────────────────────────
    contact: {
        badge: 'Liên hệ',
        headline: 'Liên hệ với chúng tôi',
        subtitle: 'Bạn có câu hỏi hoặc phản hồi? Chúng tôi rất muốn nghe từ bạn.',
        reachUs: 'Liên hệ trực tiếp',
        businessHours: 'Giờ làm việc',
        hoursValue: 'Thứ Hai – Thứ Sáu: 9:00 – 18:00 (UTC+7)',
        hoursNote: 'Chúng tôi phản hồi mọi yêu cầu trong vòng 24 giờ làm việc.',
        form: {
            title: 'Gửi tin nhắn',
            name: 'Họ và tên',
            email: 'Email',
            subject: 'Chủ đề',
            selectTopic: 'Chọn chủ đề',
            message: 'Nội dung',
            messagePlaceholder: 'Cho chúng tôi biết cách có thể hỗ trợ bạn…',
            send: 'Gửi tin nhắn',
            sending: 'Đang gửi…',
            sentTitle: 'Đã gửi tin nhắn!',
            sentBody: 'Cảm ơn bạn đã liên hệ. Chúng tôi sẽ phản hồi sớm nhất có thể.',
            sendAnother: 'Gửi tin nhắn khác',
        },
        topics: ['Câu hỏi chung', 'Câu hỏi về khóa học', 'Hỗ trợ kỹ thuật', 'Thanh toán & hóa đơn', 'Trở thành giảng viên', 'Khác'],
    },

    // ─── Policy ─────────────────────────────────────────────────────
    policy: {
        badge: 'Pháp lý',
        headline: 'Chính sách bảo mật',
        updated: 'Cập nhật lần cuối: 18 tháng 4, 2026',
        contactNote: 'Nếu bạn có câu hỏi về Chính sách này, vui lòng liên hệ:',
        contactUs: 'Liên hệ về quyền riêng tư',
        sections: [
            { title: '1. Thông tin chúng tôi thu thập', body: 'Chúng tôi thu thập thông tin bạn cung cấp trực tiếp, bao gồm tên, email, ngày sinh, giới tính và sở thích học tập khi bạn tạo tài khoản hoặc cập nhật hồ sơ. Chúng tôi cũng thu thập thông tin về hoạt động của bạn trên nền tảng, bao gồm các khóa học bạn đăng ký, tiến độ học tập và đánh giá bạn gửi.' },
            { title: '2. Cách chúng tôi sử dụng thông tin', body: 'Chúng tôi sử dụng thông tin thu thập để cung cấp, duy trì và cải thiện dịch vụ; cá nhân hóa trải nghiệm học tập; liên lạc với bạn về khóa học, cập nhật và khuyến mãi; đồng thời đảm bảo tính bảo mật và toàn vẹn của nền tảng.' },
            { title: '3. Chia sẻ thông tin', body: 'Chúng tôi không bán hoặc cho thuê thông tin cá nhân của bạn cho bên thứ ba. Chúng tôi có thể chia sẻ dữ liệu ẩn danh hoặc tổng hợp không xác định cá nhân. Chúng tôi có thể chia sẻ thông tin với các nhà cung cấp dịch vụ hỗ trợ vận hành nền tảng, theo nghĩa vụ bảo mật phù hợp.' },
            { title: '4. Cookie', body: 'Chúng tôi sử dụng cookie và các công nghệ theo dõi tương tự để cải thiện trải nghiệm duyệt web, phân tích lưu lượng truy cập và hiểu nguồn gốc đối tượng của chúng tôi. Bạn có thể kiểm soát cài đặt cookie qua tùy chọn trình duyệt.' },
            { title: '5. Bảo mật dữ liệu', body: 'Chúng tôi triển khai các biện pháp bảo mật theo tiêu chuẩn ngành, bao gồm mã hóa, máy chủ an toàn và kiểm tra bảo mật định kỳ để bảo vệ thông tin cá nhân của bạn.' },
            { title: '6. Lưu trữ dữ liệu', body: 'Chúng tôi lưu trữ thông tin cá nhân của bạn trong khi tài khoản còn hoạt động hoặc cần thiết để cung cấp dịch vụ. Bạn có thể yêu cầu xóa tài khoản và dữ liệu liên quan bất cứ lúc nào.' },
            { title: '7. Quyền của bạn', body: 'Tùy thuộc vào vị trí của bạn, bạn có thể có quyền truy cập, chỉnh sửa hoặc xóa dữ liệu cá nhân, phản đối hoặc hạn chế một số xử lý nhất định và tính di động dữ liệu. Để thực hiện các quyền này, vui lòng liên hệ với chúng tôi.' },
            { title: '8. Quyền riêng tư trẻ em', body: 'Dịch vụ của chúng tôi không dành cho trẻ em dưới 13 tuổi. Chúng tôi không cố ý thu thập thông tin cá nhân từ trẻ em. Nếu bạn cho rằng chúng tôi đã vô tình thu thập thông tin như vậy, vui lòng liên hệ ngay.' },
            { title: '9. Thay đổi chính sách', body: 'Chúng tôi có thể cập nhật Chính sách Bảo mật này theo thời gian. Chúng tôi sẽ thông báo cho bạn về những thay đổi quan trọng qua email hoặc thông báo nổi bật trên nền tảng. Tiếp tục sử dụng dịch vụ sau khi thay đổi có hiệu lực đồng nghĩa với việc chấp nhận chính sách đã cập nhật.' },
        ],
    },

    // ─── Admin Dashboard ─────────────────────────────────────────────
    adminDashboard: {
        title: 'Tổng quan',
        growth: 'Tăng trưởng',
        lessonGrowth: 'Tăng trưởng bài học trong 6 tháng',
        last6Months: '6 tháng qua',
        recentUsers: 'Người dùng gần đây',
        noUsers: 'Chưa có người dùng',
    },

    // ─── Admin Users ─────────────────────────────────────────────────
    adminUsers: {
        title: 'Người dùng',
        name: 'Họ và tên',
        email: 'Email',
        role: 'Vai trò',
        joined: 'Ngày tham gia',
        search: 'Tìm kiếm người dùng…',
        roleStudent: 'Học viên',
        roleInstructor: 'Giảng viên',
        roleAdmin: 'Quản trị viên',
    },

    // ─── Admin general ───────────────────────────────────────────────
    admin: {
        nav: {
            dashboard: 'Tổng quan',
            users: 'Người dùng',
            categories: 'Danh mục',
            courses: 'Khóa học',
        },
        header: {
            operations: 'Vận hành',
            title: 'Quản trị hệ thống',
            today: 'Hôm nay',
            notifications: 'Thông báo',
            markAllRead: 'Đánh dấu đã đọc',
            noNotifications: 'Chưa có thông báo',
            logout: 'Đăng xuất',
        },
    },

    // ─── Language switcher ───────────────────────────────────────────
    lang: {
        label: 'Ngôn ngữ',
        vi: 'Tiếng Việt',
        en: 'English',
        ja: '日本語',
    },
};
