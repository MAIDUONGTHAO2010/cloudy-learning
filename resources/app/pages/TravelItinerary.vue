<template>
    <div class="min-h-screen bg-gray-50 text-gray-900">
        <Navbar />

        <!-- Hero -->
        <section class="bg-gradient-to-br from-[#1a1a4e] via-[#0f2460] to-[#0c1d50] py-20 text-center">
            <div class="mx-auto max-w-3xl px-6">
                <span class="inline-block rounded-full bg-orange-500/10 px-4 py-1 text-xs font-semibold uppercase tracking-widest text-orange-400">
                    ✈️ Hành trình du lịch
                </span>
                <h1 class="mt-4 text-4xl font-bold leading-tight text-white">Khám phá Đà Nẵng – Hội An</h1>
                <p class="mt-4 text-lg text-blue-100/80 leading-relaxed">
                    Lịch trình 3 ngày trải nghiệm bãi biển, phố cổ và ẩm thực miền Trung
                </p>
                <div class="mt-6 flex flex-wrap justify-center gap-4 text-sm text-blue-100/70">
                    <span class="flex items-center gap-1"><span>📅</span> 3 ngày 2 đêm</span>
                    <span class="flex items-center gap-1"><span>👥</span> 2–4 người</span>
                    <span class="flex items-center gap-1"><span>📍</span> Đà Nẵng – Hội An</span>
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
                                <h2 class="text-xl font-bold text-gray-900">{{ days[activeDay].title }}</h2>
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
                                        <span>{{ activity }}</span>
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
                            ← Ngày {{ activeDay }}
                        </button>
                        <div v-else></div>
                        <button
                            v-if="activeDay < days.length - 1"
                            @click="activeDay++"
                            class="flex items-center gap-2 rounded-xl bg-orange-500 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-orange-600"
                        >
                            Ngày {{ activeDay + 2 }} →
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
    activities: string[];
    tip?: string;
}

interface Day {
    label: string;
    title: string;
    emoji: string;
    summary: string;
    tags: string[];
    slots: Slot[];
}

const days: Day[] = [
    {
        label: 'Ngày 1',
        title: 'Khám phá Đà Nẵng',
        emoji: '🌊',
        summary: 'Ngày đầu tiên đặt chân đến thành phố biển – check-in, tham quan cầu Rồng và tắm biển Mỹ Khê.',
        tags: ['Biển', 'Cầu Rồng', 'Hải sản'],
        slots: [
            {
                period: 'Buổi Sáng',
                time: '07:00 – 11:30',
                icon: '🌅',
                bgColor: 'bg-amber-100',
                badgeClass: 'bg-amber-100 text-amber-700',
                title: 'Bay đến Đà Nẵng & nhận phòng',
                description: 'Di chuyển từ sân bay Đà Nẵng về khách sạn, nhận phòng và nghỉ ngơi lấy sức.',
                activities: [
                    'Đón xe từ sân bay Đà Nẵng (30 phút)',
                    'Check-in khách sạn khu vực biển Mỹ Khê',
                    'Ăn sáng tại khách sạn hoặc quán mì Quảng gần đó',
                    'Dạo bộ dọc bờ biển buổi sáng',
                ],
                tip: 'Đặt phòng khách sạn tầng cao để ngắm view biển đẹp hơn.',
            },
            {
                period: 'Buổi Chiều',
                time: '13:00 – 18:00',
                icon: '☀️',
                bgColor: 'bg-sky-100',
                badgeClass: 'bg-sky-100 text-sky-700',
                title: 'Tắm biển Mỹ Khê & tham quan Cầu Rồng',
                description: 'Tận hưởng bãi biển được CNN bình chọn là một trong những bãi biển đẹp nhất hành tinh.',
                activities: [
                    'Tắm biển Mỹ Khê (1–2 giờ)',
                    'Chụp ảnh tại Cầu Rồng',
                    'Thăm Bảo tàng Chăm (tùy chọn)',
                    'Dạo phố Bạch Đằng dọc sông Hàn',
                ],
                tip: 'Mang kem chống nắng SPF 50+, biển Đà Nẵng nắng rất gắt từ 10h–16h.',
            },
            {
                period: 'Buổi Tối',
                time: '18:30 – 22:00',
                icon: '🌙',
                bgColor: 'bg-indigo-100',
                badgeClass: 'bg-indigo-100 text-indigo-700',
                title: 'Ẩm thực tối & xem Cầu Rồng phun lửa',
                description: 'Thưởng thức hải sản tươi sống rồi đến xem màn trình diễn phun lửa đặc sắc của Cầu Rồng.',
                activities: [
                    'Ăn tối tại nhà hàng hải sản Trần Phú',
                    'Đặc sản: tôm hùm, cua ghẹ, cá ngừ đại dương',
                    'Đến Cầu Rồng lúc 21:00 xem phun lửa / phun nước (Thứ 7, CN)',
                    'Cà phê view sông Hàn về đêm',
                ],
                tip: 'Cầu Rồng chỉ phun lửa vào tối Thứ 7 và Chủ Nhật lúc 21:00. Nên đến trước 20:30.',
            },
        ],
    },
    {
        label: 'Ngày 2',
        title: 'Bà Nà Hills & Phố cổ Hội An',
        emoji: '🏔️',
        summary: 'Buổi sáng chinh phục Bà Nà Hills trên mây, buổi chiều và tối đắm chìm trong vẻ đẹp cổ kính của Hội An.',
        tags: ['Bà Nà Hills', 'Hội An', 'Đèn lồng'],
        slots: [
            {
                period: 'Buổi Sáng',
                time: '07:30 – 12:00',
                icon: '🌄',
                bgColor: 'bg-amber-100',
                badgeClass: 'bg-amber-100 text-amber-700',
                title: 'Bà Nà Hills – Cầu Vàng',
                description: 'Di chuyển lên Bà Nà Hills bằng cáp treo dài nhất Đông Nam Á và chiêm ngưỡng Cầu Vàng nổi tiếng thế giới.',
                activities: [
                    'Khởi hành từ khách sạn lúc 7:30',
                    'Mua vé cáp treo Bà Nà Hills',
                    'Tham quan Cầu Vàng – chụp ảnh sống ảo',
                    'Khám phá làng Pháp cổ trên đỉnh núi',
                    'Trải nghiệm Fantasy Park',
                ],
                tip: 'Mang áo khoác mỏng vì trên đỉnh Bà Nà nhiệt độ chỉ 15–20°C, lạnh hơn dưới chân núi nhiều.',
            },
            {
                period: 'Buổi Chiều',
                time: '13:30 – 18:00',
                icon: '🏮',
                bgColor: 'bg-sky-100',
                badgeClass: 'bg-sky-100 text-sky-700',
                title: 'Hội An phố cổ',
                description: 'Di chuyển đến Hội An (30 km từ Đà Nẵng) – Di sản văn hóa thế giới UNESCO với những con phố đèn lồng rực rỡ.',
                activities: [
                    'Di chuyển Bà Nà – Hội An (~45 phút)',
                    'Tham quan Chùa Cầu Nhật Bản',
                    'Khám phá Hội quán Phúc Kiến',
                    'Dạo phố cổ Nguyễn Thái Học, Trần Phú',
                    'Mua sắm đồ thủ công mỹ nghệ, đèn lồng',
                ],
                tip: 'Nên mặc đồ lịch sự khi vào các hội quán và chùa trong phố cổ.',
            },
            {
                period: 'Buổi Tối',
                time: '18:30 – 22:00',
                icon: '✨',
                bgColor: 'bg-indigo-100',
                badgeClass: 'bg-indigo-100 text-indigo-700',
                title: 'Thả đèn hoa đăng & ẩm thực Hội An',
                description: 'Thưởng thức cao lầu, mì Quảng đặc sản Hội An rồi thả đèn hoa đăng trên sông Hoài lung linh.',
                activities: [
                    'Ăn tối: Cao lầu Hội An, Cơm gà Bà Buội',
                    'Mua đèn hoa đăng và ra bờ sông Hoài',
                    'Thả đèn và ngắm đèn lồng phố cổ về đêm',
                    'Uống trà hoặc cà phê view sông',
                ],
                tip: 'Đêm rằm âm lịch, phố cổ Hội An tắt điện thắp nến – không khí cực kỳ huyền ảo.',
            },
        ],
    },
    {
        label: 'Ngày 3',
        title: 'Ngũ Hành Sơn & Mua sắm',
        emoji: '🗿',
        summary: 'Buổi sáng leo Ngũ Hành Sơn huyền bí, chiều tham quan làng nghề và mua sắm, tối chia tay thành phố biển.',
        tags: ['Ngũ Hành Sơn', 'Làng đá', 'Shopping'],
        slots: [
            {
                period: 'Buổi Sáng',
                time: '07:30 – 11:30',
                icon: '⛰️',
                bgColor: 'bg-amber-100',
                badgeClass: 'bg-amber-100 text-amber-700',
                title: 'Ngũ Hành Sơn – Thạch động huyền bí',
                description: 'Khám phá quần thể núi đá vôi Ngũ Hành Sơn với nhiều hang động, chùa chiền ẩn sâu trong lòng núi.',
                activities: [
                    'Di chuyển đến Ngũ Hành Sơn (20 phút từ trung tâm)',
                    'Leo bộ hoặc đi thang máy lên Thủy Sơn',
                    'Tham quan động Huyền Không, chùa Tam Thai',
                    'Ngắm toàn cảnh Đà Nẵng từ đỉnh núi',
                    'Thăm làng điêu khắc đá Non Nước',
                ],
                tip: 'Đi giày đế bằng hoặc sneaker khi leo núi. Mang theo nước uống.',
            },
            {
                period: 'Buổi Chiều',
                time: '13:00 – 17:30',
                icon: '🛍️',
                bgColor: 'bg-sky-100',
                badgeClass: 'bg-sky-100 text-sky-700',
                title: 'Mua sắm & thư giãn',
                description: 'Tranh thủ mua đồ lưu niệm, đặc sản và nghỉ ngơi trước khi về.',
                activities: [
                    'Mua đặc sản: bánh tráng cuốn thịt heo, mắm Đà Nẵng, nước mắm Nam Ô',
                    'Tham quan chợ Hàn hoặc chợ Cồn',
                    'Ghé Lotte Mart / Vincom mua sắm thêm',
                    'Nghỉ ngơi tại khách sạn, check-out',
                ],
                tip: 'Giá tại chợ thường rẻ hơn các cửa hàng, nhớ mặc cả nhé!',
            },
            {
                period: 'Buổi Tối',
                time: '18:00 – 21:00',
                icon: '🍽️',
                bgColor: 'bg-indigo-100',
                badgeClass: 'bg-indigo-100 text-indigo-700',
                title: 'Bữa tối chia tay & ra sân bay',
                description: 'Bữa tối cuối cùng tại Đà Nẵng với những đặc sản không thể bỏ lỡ, rồi ra sân bay về nhà.',
                activities: [
                    'Ăn tối: Bánh xèo bà Dưỡng, bún mắm nêm, bánh mì Phượng',
                    'Dạo bờ biển lần cuối, ngắm hoàng hôn',
                    'Di chuyển ra sân bay Đà Nẵng',
                    'Bay về – kết thúc hành trình tuyệt vời',
                ],
                tip: 'Nên ra sân bay trước ít nhất 90 phút cho chuyến bay nội địa.',
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
