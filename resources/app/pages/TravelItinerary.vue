<template>
    <div class="min-h-screen bg-gray-50 text-gray-900">
        <Navbar />

        <!-- Hero -->
        <section class="bg-gradient-to-br from-[#1a1a4e] via-[#0f2460] to-[#0c1d50] py-20 text-center">
            <div class="mx-auto max-w-3xl px-6">
                <span class="inline-block rounded-full bg-orange-500/10 px-4 py-1 text-xs font-semibold uppercase tracking-widest text-orange-400">
                    🌴 Hành trình du lịch
                </span>
                <h1 class="mt-4 text-4xl font-bold leading-tight text-white">Khám phá Nha Trang</h1>
                <p class="mt-4 text-lg text-blue-100/80 leading-relaxed">
                    Lịch trình 29/04 – 03/05/2026 · Biển xanh, Vinpearl, ẩm thực và tiệc cưới
                </p>
                <div class="mt-6 flex flex-wrap justify-center gap-4 text-sm text-blue-100/70">
                    <span class="flex items-center gap-1"><span>📅</span> 29/04 – 03/05/2026</span>
                    <span class="flex items-center gap-1"><span>🏨</span> Daisy Flower Hotel</span>
                    <span class="flex items-center gap-1"><span>📍</span> Nha Trang, Khánh Hoà</span>
                </div>
            </div>
        </section>

        <!-- Day Tabs -->
        <div class="sticky top-0 z-20 border-b border-gray-200 bg-white shadow-sm">
            <div class="mx-auto max-w-4xl px-4">
                <nav class="flex" aria-label="Tabs">
                    <button
                        v-for="(day, index) in days"
                        :key="index"
                        @click="activeDay = index"
                        :class="[
                            'flex-1 border-b-2 py-4 text-sm font-semibold transition-colors',
                            activeDay === index
                                ? 'border-orange-500 text-orange-600'
                                : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700',
                        ]"
                    >
                        <span class="block text-xs font-normal text-gray-400">{{ day.label }}</span>
                        {{ day.title }}
                    </button>
                </nav>
            </div>
        </div>

        <!-- Day Content -->
        <main class="mx-auto max-w-4xl px-4 py-10">
            <Transition name="slide-fade" mode="out-in">
                <div :key="activeDay">
                    <!-- Overview card -->
                    <div class="mb-8 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                        <div class="flex items-start gap-4">
                            <span class="text-4xl">{{ days[activeDay].emoji }}</span>
                            <div>
                                <p class="text-xs font-medium text-orange-500">{{ days[activeDay].date }}</p>
                                <h2 class="mt-0.5 text-xl font-bold text-gray-900">{{ days[activeDay].title }}</h2>
                                <p class="mt-1 text-gray-500">{{ days[activeDay].summary }}</p>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    <span
                                        v-for="tag in days[activeDay].tags"
                                        :key="tag"
                                        class="rounded-full bg-orange-50 px-3 py-0.5 text-xs font-medium text-orange-600"
                                    >
                                        {{ tag }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Timeline blocks: Morning / Afternoon / Evening -->
                    <div class="relative space-y-0">
                        <!-- Vertical line -->
                        <div class="absolute left-[1.75rem] top-0 bottom-0 w-0.5 bg-gray-200 md:left-[2.25rem]"></div>

                        <div
                            v-for="(slot, si) in days[activeDay].slots"
                            :key="si"
                            class="relative pl-16 pb-10 md:pl-20"
                        >
                            <!-- Time dot -->
                            <div
                                :class="[
                                    'absolute left-0 flex h-14 w-14 flex-shrink-0 items-center justify-center rounded-full border-4 border-white shadow md:h-[4.5rem] md:w-[4.5rem]',
                                    slot.bgColor,
                                ]"
                            >
                                <span class="text-xl md:text-2xl">{{ slot.icon }}</span>
                            </div>

                            <!-- Card -->
                            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                                <div class="mb-4 flex items-center gap-2">
                                    <span :class="['rounded-full px-3 py-1 text-xs font-semibold', slot.badgeClass]">
                                        {{ slot.period }}
                                    </span>
                                    <span class="text-xs text-gray-400">{{ slot.time }}</span>
                                </div>

                                <h3 class="text-lg font-bold text-gray-900">{{ slot.title }}</h3>
                                <p class="mt-2 text-sm leading-relaxed text-gray-500">{{ slot.description }}</p>

                                <!-- Activities list -->
                                <ul class="mt-4 space-y-2">
                                    <li
                                        v-for="(activity, ai) in slot.activities"
                                        :key="ai"
                                        class="flex items-start gap-2 text-sm text-gray-700"
                                    >
                                        <span class="mt-0.5 text-orange-400">▸</span>
                                        <span>
                                            {{ activity.label }}
                                            <span v-if="activity.location" class="ml-1 inline-flex items-center gap-0.5 text-xs text-gray-400">
                                                <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                                                {{ activity.location }}
                                            </span>
                                        </span>
                                    </li>
                                </ul>

                                <!-- Tips -->
                                <div v-if="slot.tip" class="mt-4 rounded-xl bg-amber-50 p-3 text-xs text-amber-700">
                                    <span class="font-semibold">💡 Gợi ý: </span>{{ slot.tip }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Day navigation -->
                    <div class="mt-4 flex justify-between">
                        <button
                            v-if="activeDay > 0"
                            @click="activeDay--"
                            class="flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50"
                        >
                            ← {{ days[activeDay - 1].label }}
                        </button>
                        <div v-else></div>
                        <button
                            v-if="activeDay < days.length - 1"
                            @click="activeDay++"
                            class="flex items-center gap-2 rounded-xl bg-orange-500 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-orange-600"
                        >
                            {{ days[activeDay + 1].label }} →
                        </button>
                    </div>
                </div>
            </Transition>
        </main>

        <AppFooter />
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import Navbar from '../components/Navbar.vue';
import AppFooter from '../components/Footer.vue';

const activeDay = ref(0);

interface Slot {
    period: string;
    time: string;
    icon: string;
    bgColor: string;
    badgeClass: string;
    title: string;
    description: string;
    activities: { label: string; location?: string; type?: string }[];
    tip?: string;
}

interface Day {
    label: string;
    date: string;
    title: string;
    emoji: string;
    summary: string;
    tags: string[];
    slots: Slot[];
}

const days: Day[] = [
    {
        label: '29/04',
        date: 'Thứ Tư, 29/04/2026',
        title: 'Ngày khởi hành',
        emoji: '🚗',
        summary: 'Lên đường đến Nha Trang – khởi đầu chuyến hành trình đầy hứng khởi.',
        tags: ['Di chuyển'],
        slots: [
            {
                period: 'Buổi Sáng',
                time: 'Cả ngày',
                icon: '🌅',
                bgColor: 'bg-amber-100',
                badgeClass: 'bg-amber-100 text-amber-700',
                title: 'Lên đường',
                description: 'Khởi hành đến Nha Trang bằng xe.',
                activities: [{ label: 'Đi xe đến Nha Trang', type: 'Di chuyển' }],
                tip: 'Chuẩn bị đồ ăn nhẹ và nước uống cho chuyến đi dài.',
            },
            {
                period: 'Buổi Chiều',
                time: '',
                icon: '☀️',
                bgColor: 'bg-sky-100',
                badgeClass: 'bg-sky-100 text-sky-700',
                title: 'Trên đường',
                description: 'Tiếp tục hành trình di chuyển.',
                activities: [{ label: 'Nghỉ dọc đường, ngắm cảnh' }],
            },
            {
                period: 'Buổi Tối',
                time: '',
                icon: '🌙',
                bgColor: 'bg-indigo-100',
                badgeClass: 'bg-indigo-100 text-indigo-700',
                title: 'Đến nơi & nghỉ ngơi',
                description: 'Đến Nha Trang, ổn định chỗ ở và nghỉ ngơi lấy sức.',
                activities: [{ label: 'Check-in Daisy Flower Hotel', location: 'Daisy Flower Hotel' }],
            },
        ],
    },
    {
        label: '30/04',
        date: 'Thứ Năm, 30/04/2026',
        title: 'Khám phá Nha Trang',
        emoji: '🌊',
        summary: 'Nhận xe máy, thưởng thức ẩm thực đặc trưng, tham quan Nhà hát Đỏ và vui chơi về đêm.',
        tags: ['Ẩm thực', 'Tham quan', 'Nightlife'],
        slots: [
            {
                period: 'Buổi Sáng',
                time: 'Sáng sớm',
                icon: '🌅',
                bgColor: 'bg-amber-100',
                badgeClass: 'bg-amber-100 text-amber-700',
                title: 'Nhận xe & ăn sáng đặc sản',
                description: 'Nhận xe máy tại khách sạn, cất hành lý và bắt đầu ngày mới với cà phê view biển và bánh căn mực đặc trưng Nha Trang.',
                activities: [
                    { label: 'Nhận xe máy tại Daisy Flower Hotel', location: 'Daisy Flower Hotel', type: 'Khác' },
                    { label: 'Cất hành lý', type: 'Khác' },
                    { label: 'Cà phê Hòn Chồng – view biển tuyệt đẹp', type: 'Ăn uống' },
                    { label: 'Ăn bánh căn mực Cô Loan', location: 'Bánh căn mực Cô Loan', type: 'Ăn uống' },
                ],
                tip: 'Bánh căn mực Cô Loan là đặc sản không thể bỏ qua – nên đến sớm trước 9h để tránh hết hàng.',
            },
            {
                period: 'Buổi Chiều',
                time: 'Chiều',
                icon: '☀️',
                bgColor: 'bg-sky-100',
                badgeClass: 'bg-sky-100 text-sky-700',
                title: 'Nhà hát Đỏ & nhận phòng',
                description: 'Tham quan Nhà hát Đỏ – công trình kiến trúc ấn tượng tại trung tâm Nha Trang, sau đó chính thức nhận phòng nghỉ ngơi.',
                activities: [
                    { label: 'Tham quan Nhà hát Đỏ', type: 'Khác' },
                    { label: 'Nhận phòng khách sạn', location: 'Daisy Flower Hotel' },
                    { label: 'Nghỉ ngơi, tắm biển (tùy chọn)' },
                ],
                tip: 'Nhà hát Đỏ (Trung tâm Hội nghị tỉnh Khánh Hoà) có kiến trúc đặc trưng đỏ nổi bật – điểm check-in đẹp.',
            },
            {
                period: 'Buổi Tối',
                time: 'Tối – Đêm',
                icon: '🌙',
                bgColor: 'bg-indigo-100',
                badgeClass: 'bg-indigo-100 text-indigo-700',
                title: 'Nhậu Lương Sơn & đi Bar',
                description: 'Tối đến khu Lương Sơn nổi tiếng với hải sản tươi sống, sau đó tận hưởng không khí về đêm Nha Trang.',
                activities: [
                    { label: 'Ăn tối & nhậu tại khu Lương Sơn', location: 'Lương Sơn', type: 'Ăn uống' },
                    { label: 'Khám phá bar & nightlife Nha Trang' },
                ],
                tip: 'Khu Lương Sơn có nhiều quán hải sản tươi, giá cả bình dân – nên hỏi giá trước khi gọi món.',
            },
        ],
    },
    {
        label: '01/05',
        date: 'Thứ Sáu, 01/05/2026',
        title: 'Vinpearl cả ngày',
        emoji: '🎡',
        summary: 'Cả ngày vui chơi tại Vinpearl Land – cáp treo, zipline, thuỷ cung, show nhạc nước và nhiều trò giải trí đỉnh cao.',
        tags: ['Vinpearl', 'Giải trí', 'Show diễn'],
        slots: [
            {
                period: 'Buổi Sáng',
                time: '8:30 – 12:00',
                icon: '🎢',
                bgColor: 'bg-amber-100',
                badgeClass: 'bg-amber-100 text-amber-700',
                title: 'Cáp treo & khu phiêu lưu',
                description: 'Lên Vinpearl bằng cáp treo biển dài nhất thế giới, trải nghiệm những trò chơi mạo hiểm đầu ngày.',
                activities: [
                    { label: 'Di chuyển đến bến cáp treo Vinpearl', location: 'Vinpearl Nha Trang' },
                    { label: '8:30 xếp hàng lên cáp treo – nên đến sớm!' },
                    { label: 'Zipline – cảm giác bay trên không' },
                    { label: 'Trượt núi' },
                    { label: 'Phiêu lưu hầm mỏ' },
                ],
                tip: 'Đến trước 8:30 để tránh hàng dài. Mang giày thể thao để chơi các trò mạo hiểm.',
            },
            {
                period: 'Buổi Chiều',
                time: '12:00 – 17:30',
                icon: '🌊',
                bgColor: 'bg-sky-100',
                badgeClass: 'bg-sky-100 text-sky-700',
                title: 'Thuỷ cung, rạp phim 360° & công viên',
                description: 'Khám phá thuỷ cung đẳng cấp, trải nghiệm rạp phim 360° độc đáo và dạo quanh các khu vui chơi.',
                activities: [
                    { label: 'Thuỷ cung Vinpearl' },
                    { label: 'Ăn trưa tại khu ẩm thực Vinpearl' },
                    { label: 'Rạp phim 360°' },
                    { label: 'Ta Ta River' },
                    { label: 'Bánh xe bầu trời (Ferris Wheel)' },
                    { label: 'King Garden – vườn hoa đẹp' },
                ],
                tip: 'Ưu tiên thuỷ cung vào đầu chiều – buổi chiều ít người hơn buổi sáng.',
            },
            {
                period: 'Buổi Tối',
                time: '18:00 – 23:00',
                icon: '🎆',
                bgColor: 'bg-indigo-100',
                badgeClass: 'bg-indigo-100 text-indigo-700',
                title: 'Show nhạc nước, Tata Show & ăn khuya',
                description: 'Kết thúc ngày tại Vinpearl với show nhạc nước hoành tráng, Tata Show đặc sắc, rồi về ăn khuya gần Tháp Bà.',
                activities: [
                    { label: 'Ăn tối trong khuôn viên Vinpearl' },
                    { label: 'Xem Show nhạc nước' },
                    { label: 'Xem Tata Show' },
                    { label: 'Ăn khuya gần Tháp Bà Ponagar', location: 'Tháp Bà Ponagar' },
                    { label: 'Về lại Daisy Flower Hotel', location: 'Daisy Flower Hotel' },
                ],
                tip: 'Show nhạc nước và Tata Show thường diễn vào tối – kiểm tra lịch cụ thể tại quầy thông tin Vinpearl.',
            },
        ],
    },
    {
        label: '02/05',
        date: 'Thứ Bảy, 02/05/2026',
        title: 'Bình minh & Đám cưới',
        emoji: '💒',
        summary: 'Ngắm bình minh trên biển, ăn sáng tại Sailing Club sang trọng, rồi tham dự đám cưới.',
        tags: ['Bình minh', 'Ăn sáng', 'Đám cưới'],
        slots: [
            {
                period: 'Buổi Sáng',
                time: 'Sáng sớm',
                icon: '🌅',
                bgColor: 'bg-amber-100',
                badgeClass: 'bg-amber-100 text-amber-700',
                title: 'Bình minh & ăn sáng Sailing Club',
                description: 'Dậy sớm ra bờ biển ngắm bình minh Nha Trang – khoảnh khắc không thể quên, rồi ăn sáng tại Sailing Club nổi tiếng.',
                activities: [
                    { label: 'Đi ngắm bình minh trên bờ biển Nha Trang' },
                    { label: 'Ăn sáng tại Sailing Club', location: 'Sailing Club Nha Trang' },
                ],
                tip: 'Bình minh Nha Trang khoảng 5:30–6:00 sáng – cực đẹp và yên tĩnh. Mang máy ảnh nhé!',
            },
            {
                period: 'Buổi Chiều',
                time: 'Chiều',
                icon: '💐',
                bgColor: 'bg-sky-100',
                badgeClass: 'bg-sky-100 text-sky-700',
                title: 'Đám cưới',
                description: 'Tham dự tiệc cưới – chúc mừng cô dâu chú rể trong không khí vui tươi.',
                activities: [
                    { label: 'Đi đám cưới' },
                    { label: 'Về lại khách sạn nghỉ ngơi' },
                ],
            },
            {
                period: 'Buổi Tối',
                time: 'Tối',
                icon: '🥂',
                bgColor: 'bg-indigo-100',
                badgeClass: 'bg-indigo-100 text-indigo-700',
                title: 'Tiệc cưới buổi tối',
                description: 'Tiếp tục tham dự đám cưới buổi tối.',
                activities: [
                    { label: 'Tham dự tiệc cưới tối' },
                ],
            },
        ],
    },
    {
        label: '03/05',
        date: 'Chủ Nhật, 03/05/2026',
        title: 'Buổi sáng cuối & về nhà',
        emoji: '☕',
        summary: 'Buổi sáng thong thả tại Duyên Hà Coffee trước khi kết thúc hành trình đáng nhớ.',
        tags: ['Cà phê', 'Về nhà'],
        slots: [
            {
                period: 'Buổi Sáng',
                time: 'Sáng',
                icon: '☕',
                bgColor: 'bg-amber-100',
                badgeClass: 'bg-amber-100 text-amber-700',
                title: 'Ăn sáng & cà phê chia tay',
                description: 'Thong thả buổi sáng cuối tại Duyên Hà Coffee trước khi lên đường về.',
                activities: [
                    { label: 'Đi ăn sáng tại Duyên Hà Coffee', location: 'Duyên Hà Coffee' },
                    { label: 'Uống cà phê, ngắm biển lần cuối' },
                ],
                tip: 'Duyên Hà Coffee có view biển đẹp – một điểm kết thúc hoàn hảo cho chuyến đi.',
            },
            {
                period: 'Buổi Chiều',
                time: 'Chiều',
                icon: '🏠',
                bgColor: 'bg-sky-100',
                badgeClass: 'bg-sky-100 text-sky-700',
                title: 'Check-out & về nhà',
                description: 'Trả phòng, thu dọn hành lý và lên đường về.',
                activities: [
                    { label: 'Check-out khách sạn' },
                    { label: 'Mua thêm đặc sản làm quà (yến sào, nem Ninh Hoà…)' },
                    { label: 'Lên đường về nhà, mang theo kỷ niệm đẹp' },
                ],
            },
            {
                period: 'Buổi Tối',
                time: '',
                icon: '🌙',
                bgColor: 'bg-indigo-100',
                badgeClass: 'bg-indigo-100 text-indigo-700',
                title: 'Về đến nhà',
                description: 'Kết thúc hành trình Nha Trang tuyệt vời.',
                activities: [{ label: 'An toàn về đến nhà 🏡' }],
            },
        ],
    },
];
</script>

<style scoped>
.slide-fade-enter-active,
.slide-fade-leave-active {
    transition: all 0.25s ease;
}
.slide-fade-enter-from {
    opacity: 0;
    transform: translateX(16px);
}
.slide-fade-leave-to {
    opacity: 0;
    transform: translateX(-16px);
}
</style>
