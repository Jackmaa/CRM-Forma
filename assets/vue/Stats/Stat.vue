<template>
    <section class="p-6 bg-gray-50 h-full">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-semibold">Statistiques de gestion</h2>
        </div>

        <!-- KPI Cards -->
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div class="bg-white p-4 rounded shadow flex items-center">
                <Users class="w-8 h-8 text-blue-500 mr-4" />
                <div>
                    <p class="text-sm text-gray-500">Utilisateurs</p>
                    <p class="text-2xl font-bold">{{ stats.users }}</p>
                </div>
            </div>
            <div class="bg-white p-4 rounded shadow flex items-center">
                <BookOpen class="w-8 h-8 text-green-500 mr-4" />
                <div>
                    <p class="text-sm text-gray-500">Formations</p>
                    <p class="text-2xl font-bold">{{ stats.formations }}</p>
                </div>
            </div>
            <div class="bg-white p-4 rounded shadow flex items-center">
                <BarChart2 class="w-8 h-8 text-purple-500 mr-4" />
                <div>
                    <p class="text-sm text-gray-500">Rapports</p>
                    <p class="text-2xl font-bold">{{ stats.reports }}</p>
                </div>
            </div>
            <div class="bg-white p-4 rounded shadow flex items-center">
                <Activity class="w-8 h-8 text-red-500 mr-4" />
                <div>
                    <p class="text-sm text-gray-500">Sessions actives</p>
                    <p class="text-2xl font-bold">{{ stats.sessions }}</p>
                </div>
            </div>
        </div>
        <!-- Monthly Activity Chart -->
        <div class="bg-white p-6 rounded shadow over">
            <h3 class="text-lg font-semibold mb-4">Activité mensuelle</h3>
            <div style="height: 300px">
                <!-- Use correct prop names for vue-chartjs -->
                <Line
                    :data="chartData"
                    :options="chartOptions"
                    id="line-chart"
                />
            </div>
        </div>
    </section>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { Users, BookOpen, BarChart2, Activity } from "lucide-vue-next";
import {
    Chart as ChartJS,
    Title,
    Tooltip,
    Legend,
    LineElement,
    PointElement,
    CategoryScale,
    LinearScale,
} from "chart.js";
import { Line } from "vue-chartjs";

// Register Chart.js components
ChartJS.register(
    Title,
    Tooltip,
    Legend,
    LineElement,
    PointElement,
    CategoryScale,
    LinearScale
);

// KPI stats
const stats = ref({ users: 0, formations: 0, reports: 0, sessions: 0 });

// Chart data as ref
const chartData = ref({ labels: [], datasets: [] });

// Chart options
const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    scales: { y: { beginAtZero: true } },
};

onMounted(() => {
    // Replace with real API calls
    stats.value = { users: 187, formations: 42, reports: 18, sessions: 256 };

    const labels = ["Jan", "Fév", "Mar", "Avr", "Mai", "Juin"];
    const dataValues = [30, 45, 60, 50, 70, 85];

    chartData.value = {
        labels,
        datasets: [
            {
                label: "Visites",
                data: dataValues,
                fill: false,
                borderWidth: 2,
            },
        ],
    };
});
</script>
