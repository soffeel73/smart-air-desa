<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { 
  ArrowRightOnRectangleIcon, 
  UserCircleIcon, 
  ClipboardDocumentCheckIcon,
  Bars3Icon,
  XMarkIcon
} from '@heroicons/vue/24/outline'

const router = useRouter()
const username = ref(localStorage.getItem('username') || 'Petugas')
const isSidebarOpen = ref(false)

const menuItems = [
  { 
    path: '/petugas/input-meter', 
    name: 'Input Meter', 
    icon: ClipboardDocumentCheckIcon,
    description: 'Input data meteran pelanggan'
  }
]

const handleLogout = () => {
  localStorage.removeItem('auth_token')
  localStorage.removeItem('user_role')
  localStorage.removeItem('username')
  router.push('/login')
}

const toggleSidebar = () => {
  isSidebarOpen.value = !isSidebarOpen.value
}

const navigateTo = (path) => {
  router.push(path)
  isSidebarOpen.value = false
}
</script>

<template>
  <div class="min-h-screen bg-[#F0FBFA]">
    <!-- Mobile Header -->
    <header class="lg:hidden bg-white/70 backdrop-blur-xl border-b border-gray-200 sticky top-0 z-50 shadow-sm">
      <div class="px-4 py-3 flex justify-between items-center">
        <button @click="toggleSidebar" class="p-2 rounded-lg hover:bg-accent/10 transition-colors">
          <Bars3Icon v-if="!isSidebarOpen" class="w-6 h-6 text-gray-700" />
          <XMarkIcon v-else class="w-6 h-6 text-gray-700" />
        </button>
        
        <div class="flex items-center gap-2">
          <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-accent to-orange-600 flex items-center justify-center text-white">
            <ClipboardDocumentCheckIcon class="w-5 h-5" />
          </div>
          <span class="font-bold text-gray-900">Portal Petugas</span>
        </div>
        
        <button @click="handleLogout" class="p-2 rounded-lg hover:bg-red-50 transition-colors">
          <ArrowRightOnRectangleIcon class="w-6 h-6 text-gray-600" />
        </button>
      </div>
    </header>

    <!-- Sidebar Overlay (Mobile) -->
    <div 
      v-if="isSidebarOpen" 
      @click="toggleSidebar"
      class="lg:hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-40"
    ></div>

    <!-- Sidebar -->
    <aside 
      :class="[
        'fixed top-0 left-0 h-screen bg-white border-r border-gray-200 z-50 transition-transform duration-300',
        'w-72 lg:translate-x-0',
        isSidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
      ]"
    >
      <div class="h-full flex flex-col">
        <!-- Logo/Header -->
        <div class="p-6 border-b border-gray-200">
          <div class="flex items-center gap-3 mb-4">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-accent to-orange-600 flex items-center justify-center text-white shadow-lg">
              <ClipboardDocumentCheckIcon class="w-7 h-7" />
            </div>
            <div>
              <h1 class="text-lg font-bold text-gray-900">Portal Petugas</h1>
              <p class="text-xs text-gray-500">Input Data Lapangan</p>
            </div>
          </div>
          
          <!-- User Info -->
          <div class="bg-frozen rounded-lg p-3 flex items-center gap-2">
            <UserCircleIcon class="w-8 h-8 text-accent" />
            <div class="flex-1 min-w-0">
              <p class="text-sm font-bold text-gray-900 truncate">{{ username }}</p>
              <p class="text-xs text-gray-500">Petugas Lapangan</p>
            </div>
            <div class="flex-shrink-0">
              <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
            </div>
          </div>
        </div>

        <!-- Navigation Menu -->
        <nav class="flex-1 p-4 space-y-2 overflow-y-auto">
          <button
            v-for="item in menuItems"
            :key="item.path"
            @click="navigateTo(item.path)"
            :class="[
              'w-full flex items-start gap-3 p-3 rounded-xl transition-all duration-200',
              $route.path === item.path
                ? 'bg-accent text-white shadow-lg shadow-accent/30'
                : 'text-gray-700 hover:bg-frozen hover:text-accent'
            ]"
          >
            <component 
              :is="item.icon" 
              class="w-5 h-5 flex-shrink-0 mt-0.5"
            />
            <div class="text-left">
              <div class="font-bold text-sm">{{ item.name }}</div>
              <div 
                :class="[
                  'text-xs mt-0.5',
                  $route.path === item.path ? 'text-white/80' : 'text-gray-500'
                ]"
              >
                {{ item.description }}
              </div>
            </div>
          </button>
        </nav>

        <!-- Logout Button -->
        <div class="p-4 border-t border-gray-200">
          <button 
            @click="handleLogout"
            class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-red-50 hover:bg-red-100 text-red-600 font-bold rounded-xl transition-colors"
          >
            <ArrowRightOnRectangleIcon class="w-5 h-5" />
            <span>Keluar</span>
          </button>
        </div>
      </div>
    </aside>

    <!-- Main Content Area -->
    <div class="lg:ml-72">
      <!-- Desktop Header -->
      <header class="hidden lg:block bg-white/70 backdrop-blur-xl border-b border-gray-200 sticky top-0 z-40 shadow-sm">
        <div class="px-6 py-4 flex justify-between items-center">
          <div>
            <h2 class="text-xl font-bold text-gray-900">{{ $route.meta.title || 'Input Meter' }}</h2>
            <p class="text-sm text-gray-500">{{ $route.meta.subtitle || 'Input data meteran air pelanggan' }}</p>
          </div>
          
          <div class="flex items-center gap-4">
            <div class="flex items-center gap-2 px-3 py-2 bg-frozen rounded-lg">
              <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
              <span class="text-sm font-medium text-gray-700">Online</span>
            </div>
          </div>
        </div>
      </header>

      <!-- Page Content -->
      <main class="p-4 lg:p-6">
        <router-view />
      </main>
    </div>
  </div>
</template>

<style scoped>
@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.5;
  }
}

.animate-pulse {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>
