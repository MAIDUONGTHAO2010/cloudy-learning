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
        emoji: '🚌',
        summary: 'Lên xe khách và di chuyển đến Nha Trang.',
        tags: ['Di chuyển'],
        slots: [
            {
                period: 'Cả ngày',
                time: 'Cả ngày',
                icon: '🚌',
                bgColor: 'bg-amber-100',
                badgeClass: 'bg-amber-100 text-amber-700',
                title: 'Lên xe khách đến Nha Trang',
                description: 'Lên xe khách và di chuyển đến Nha Trang.',
                activities: [{ label: 'Lên xe khách đi Nha Trang', type: 'Di chuyển' }],
                tip: 'Chuẩn bị đồ ăn nhẹ và nước uống cho chuyến đi.',
            },
        ],
    },
    {
        label: '30/04',
        date: 'Thứ Năm, 30/04/2026',
        title: 'Khám phá Nha Trang',
        emoji: '🌊',
        summary: 'Nhận xe máy, cà phê Hòn Chồng, ăn bánh căn, check-in Bờ Kè Làng Chụt, tham quan Nhà thờ Đỏ, ăn ốc Lương Sơn và nhậu tại Bar Kisho về đêm.',
        tags: ['Ẩm thực', 'Tham quan', 'Check-in', 'Nightlife'],
        slots: [
            {
                period: 'Buổi Sáng',
                time: 'Sáng sớm',
                icon: '🌅',
                bgColor: 'bg-amber-100',
                badgeClass: 'bg-amber-100 text-amber-700',
                title: 'Nhận xe, cà phê Hòn Chồng & check-in Bờ Kè Làng Chụt',
                description: 'Nhận xe máy, gửi đồ tại Daisy Hotel rồi bắt đầu ngày mới với cà phê view biển tại Hòn Chồng, ăn bánh căn đặc sản và ghé Bờ Kè Làng Chụt check-in.',
                activities: [
                    { label: 'Nhận xe máy & gửi đồ tại Daisy Hotel', location: 'Daisy Flower Hotel', type: 'Khác' },
                    { label: 'Cà phê Hòn Chồng – view biển tuyệt đẹp', location: 'Hòn Chồng', type: 'Ăn uống' },
                    { label: 'Ăn bánh căn đặc sản Nha Trang', type: 'Ăn uống' },
                    { label: 'Ghé Bờ Kè Làng Chụt check-in', location: 'Bờ Kè Làng Chụt', type: 'Tham quan' },
                ],
                tip: 'Bánh căn là đặc sản không thể bỏ qua – nên đến sớm để tránh hết hàng. Bờ Kè Làng Chụt là điểm check-in siêu đẹp ven biển.',
            },
            {
                period: 'Buổi Trưa – Chiều',
                time: 'Trưa – Chiều',
                icon: '☀️',
                bgColor: 'bg-sky-100',
                badgeClass: 'bg-sky-100 text-sky-700',
                title: 'Nhà thờ Đỏ & ăn ốc Lương Sơn',
                description: 'Ghé thăm Nhà thờ Đỏ – công trình kiến trúc nổi tiếng tại trung tâm Nha Trang, sau đó thưởng thức ốc Lương Sơn hấp dẫn rồi về lại Daisy Hotel nghỉ ngơi.',
                activities: [
                    { label: 'Ghé Nhà thờ Đỏ', location: 'Nhà thờ Đỏ Nha Trang', type: 'Tham quan' },
                    { label: 'Ăn ốc Lương Sơn', location: 'Lương Sơn', type: 'Ăn uống' },
                    { label: 'Về lại Daisy Hotel nghỉ ngơi', location: 'Daisy Flower Hotel', type: 'Khác' },
                ],
                tip: 'Nhà thờ Đỏ có kiến trúc đỏ nổi bật – điểm check-in ấn tượng giữa trung tâm thành phố. Ốc Lương Sơn đa dạng và tươi ngon.',
            },
            {
                period: 'Buổi Tối',
                time: 'Tối – Đêm',
                icon: '🌙',
                bgColor: 'bg-indigo-100',
                badgeClass: 'bg-indigo-100 text-indigo-700',
                title: 'Nhậu & Bar Kisho Nha Trang',
                description: 'Tối ra ngoài nhậu và tận hưởng không khí về đêm sôi động tại Bar Kisho Nha Trang.',
                activities: [
                    { label: 'Đi nhậu', type: 'Ăn uống' },
                    { label: 'Bar Kisho Nha Trang', location: 'Bar Kisho Nha Trang', type: 'Giải trí' },
                ],
                tip: 'Bar Kisho là điểm vui chơi về đêm nổi tiếng tại Nha Trang – không khí sôi động và thú vị.',
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
