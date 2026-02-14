<script setup>
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { 
  HomeIcon, 
  UsersIcon, 
  ClipboardDocumentCheckIcon, 
  BanknotesIcon, 
  ChartBarIcon, 
  CurrencyDollarIcon,
  Bars3Icon,
  XMarkIcon,
  NewspaperIcon,
  PrinterIcon,
  MegaphoneIcon
} from '@heroicons/vue/24/outline'

const router = useRouter()
const route = useRoute()
const sidebarOpen = ref(false)
const keluhanCount = ref(0)

const navigation = [
  { name: 'Dashboard', icon: HomeIcon, path: '/admin/dashboard' },
  { name: 'Pelanggan', icon: UsersIcon, path: '/admin/customers' },
  { name: 'Input Meter', icon: ClipboardDocumentCheckIcon, path: '/admin/meter' },
  { name: 'Data Tagihan', icon: BanknotesIcon, path: '/admin/billing' },
  { name: 'Cetak Struk', icon: PrinterIcon, path: '/admin/cetak-struk' },
  { name: 'Rekapitulasi', icon: ChartBarIcon, path: '/admin/reports' },
  { name: 'Lap. Keuangan', icon: CurrencyDollarIcon, path: '/admin/financial' },
  { name: 'Daftar Keluhan', icon: MegaphoneIcon, path: '/admin/keluhan', badge: true },
  { name: 'Manajemen Konten', icon: NewspaperIcon, path: '/admin/content' },
]

// Fetch new keluhan count
const fetchKeluhanCount = async () => {
  try {
    const response = await fetch('/api/keluhan.php?action=count_new')
    const data = await response.json()
    if (data.success) {
      keluhanCount.value = data.count
    }
  } catch (error) {
    console.error('Failed to fetch keluhan count')
  }
}

const navigateTo = (path) => {
  router.push(path)
  sidebarOpen.value = false
}

const handleLogout = () => {
  localStorage.removeItem('auth_token')
  localStorage.removeItem('user_role')
  localStorage.removeItem('username')
  router.push('/login')
}

onMounted(() => {
  fetchKeluhanCount()
  // Refresh count every 30 seconds
  setInterval(fetchKeluhanCount, 30000)
})
</script>


<template>
  <div class="min-h-screen bg-frozen flex">
    
    <!-- Mobile Sidebar Backdrop -->
    <div v-if="sidebarOpen" class="fixed inset-0 z-40 bg-gray-900/50 lg:hidden" @click="sidebarOpen = false"></div>

    <!-- Sidebar -->
    <aside 
        class="fixed inset-y-0 left-0 z-50 w-64 bg-primary text-white transition-transform duration-300 transform lg:translate-x-0 shadow-xl"
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    >
      <div class="h-20 flex items-center justify-between px-6 border-b border-white/10">
        <h1 class="text-2xl font-bold tracking-tight">Admin<span class="text-accent">Panel</span></h1>
        <button @click="sidebarOpen = false" class="lg:hidden text-white hover:text-gray-200">
           <XMarkIcon class="w-6 h-6" />
        </button>
      </div>

      <nav class="p-4 space-y-1 overflow-y-auto" style="max-height: calc(100vh - 140px);">
        <button 
            v-for="item in navigation" 
            :key="item.name"
            @click="navigateTo(item.path)"
            class="w-full flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 group relative"
            :class="route.path === item.path ? 'bg-white text-primary font-bold shadow-lg' : 'text-white hover:bg-white/10'"
        >
          <component :is="item.icon" class="w-5 h-5" :class="route.path === item.path ? 'text-primary' : 'text-white/80 group-hover:text-white'" />
          {{ item.name }}
          <!-- Badge for keluhan -->
          <span 
            v-if="item.badge && keluhanCount > 0"
            class="absolute right-3 min-w-5 h-5 flex items-center justify-center text-xs font-bold bg-red-500 text-white rounded-full px-1.5"
          >
            {{ keluhanCount > 99 ? '99+' : keluhanCount }}
          </span>
        </button>
      </nav>

      <div class="absolute bottom-0 left-0 w-full p-4 border-t border-white/10">
        <button @click="handleLogout" class="w-full flex items-center gap-3 px-4 py-3 text-white/80 hover:text-white hover:bg-white/10 rounded-xl transition-all">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
          Logout
        </button>
      </div>
    </aside>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden lg:ml-64">
        <!-- Topbar -->
        <header class="bg-white/80 backdrop-blur-md border-b border-gray-100 h-20 flex items-center justify-between px-4 lg:px-8 shadow-sm">
            <button @click="sidebarOpen = true" class="lg:hidden text-gray-500 hover:text-primary">
                <Bars3Icon class="w-6 h-6" />
            </button>
            <div class="ml-auto flex items-center gap-4">
                <div class="text-right hidden sm:block">
                    <div class="text-sm font-bold text-gray-900">Administrator</div>
                    <div class="text-xs text-gray-500">Super Admin</div>
                </div>
                <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold">
                    A
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <main class="flex-1 overflow-y-auto p-4 lg:p-8">
            <router-view />
        </main>
    </div>
  </div>
</template>
