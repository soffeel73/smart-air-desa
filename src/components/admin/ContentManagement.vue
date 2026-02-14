<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { 
  PlusIcon, 
  PencilIcon, 
  TrashIcon, 
  XMarkIcon,
  PhotoIcon,
  NewspaperIcon,
  MegaphoneIcon,
  CheckCircleIcon,
  XCircleIcon,
  PhotoIcon as PhotoIconOutline,
  CloudArrowUpIcon
} from '@heroicons/vue/24/outline'
// CKEditor removed

const API_BASE = '/api/konten.php'
const router = useRouter()

// Tab state
const activeTab = ref('berita')

// ============ BERITA DATA ============
const beritaList = ref([])
const loadingBerita = ref(false)
const showBeritaModal = ref(false)
const editModeBerita = ref(false)
const showDeleteBeritaModal = ref(false)
const deleteBeritaId = ref(null)
const uploadingFoto = ref(false)

const beritaForm = ref({
  id: null,
  judul: '',
  foto_url: '',
  ringkasan: '',
  isi_berita: '',
  tanggal_publish: new Date().toISOString().split('T')[0],
  status: 'published'
})

// ============ PENGUMUMAN DATA ============
const pengumumanList = ref([])
const loadingPengumuman = ref(false)
const showPengumumanModal = ref(false)
const editModePengumuman = ref(false)
const showDeletePengumumanModal = ref(false)
const deletePengumumanId = ref(null)

const pengumumanForm = ref({
  id: null,
  teks: '',
  status: 'aktif'
})

// ============ GALERI DATA ============
const galeriList = ref([])
const loadingGaleri = ref(false)
const showGaleriModal = ref(false) // For upload/edit
const editModeGaleri = ref(false)
const showDeleteGaleriModal = ref(false)
const deleteGaleriId = ref(null)
const uploadingGaleri = ref(false)

const galeriForm = ref({
  id: null,
  judul: '',
  caption: '',
  kategori: 'Kegiatan',
  file: null,
  previewUrl: ''
})

// ============ BERITA FUNCTIONS ============
const fetchBerita = async () => {
  loadingBerita.value = true
  try {
    const res = await fetch(`${API_BASE}?action=berita`)
    const data = await res.json()
    if (data.success) beritaList.value = data.data
  } catch (e) {
    console.error('Error:', e)
  } finally {
    loadingBerita.value = false
  }
}

const openAddBerita = () => {
  editModeBerita.value = false
  beritaForm.value = {
    id: null, judul: '', foto_url: '', ringkasan: '', isi_berita: '',
    tanggal_publish: new Date().toISOString().split('T')[0], status: 'published'
  }
  showBeritaModal.value = true
}

const openEditBerita = (item) => {
  editModeBerita.value = true
  beritaForm.value = { ...item }
  showBeritaModal.value = true
}

const saveBerita = async () => {
  if (!beritaForm.value.judul) return alert('Judul harus diisi')
  loadingBerita.value = true
  try {
    const url = editModeBerita.value ? `${API_BASE}?action=berita&id=${beritaForm.value.id}` : `${API_BASE}?action=berita`
    const res = await fetch(url, {
      method: editModeBerita.value ? 'PUT' : 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(beritaForm.value)
    })
    const data = await res.json()
    if (data.success) { showBeritaModal.value = false; fetchBerita() }
    else alert(data.message)
  } catch (e) { alert('Error saving') }
  finally { loadingBerita.value = false }
}

const confirmDeleteBerita = (id) => { deleteBeritaId.value = id; showDeleteBeritaModal.value = true }

const deleteBerita = async () => {
  try {
    const res = await fetch(`${API_BASE}?action=berita&id=${deleteBeritaId.value}`, { method: 'DELETE' })
    if ((await res.json()).success) { showDeleteBeritaModal.value = false; fetchBerita() }
  } catch (e) { console.error(e) }
}

const handleFotoUpload = async (e) => {
  const file = e.target.files[0]
  if (!file) return
  uploadingFoto.value = true
  const fd = new FormData()
  fd.append('foto', file)
  try {
    const res = await fetch(`${API_BASE}?action=upload`, { method: 'POST', body: fd })
    const data = await res.json()
    if (data.success) beritaForm.value.foto_url = data.url
    else alert(data.message)
  } catch (e) { alert('Upload error') }
  finally { uploadingFoto.value = false }
}

// ============ PENGUMUMAN FUNCTIONS ============
const fetchPengumuman = async () => {
  loadingPengumuman.value = true
  try {
    const res = await fetch(`${API_BASE}?action=pengumuman`)
    const data = await res.json()
    if (data.success) pengumumanList.value = data.data
  } catch (e) { console.error('Error:', e) }
  finally { loadingPengumuman.value = false }
}

const openAddPengumuman = () => {
  editModePengumuman.value = false
  pengumumanForm.value = { id: null, teks: '', status: 'aktif' }
  showPengumumanModal.value = true
}

const openEditPengumuman = (item) => {
  editModePengumuman.value = true
  pengumumanForm.value = { ...item }
  showPengumumanModal.value = true
}

const savePengumuman = async () => {
  if (!pengumumanForm.value.teks) return alert('Teks harus diisi')
  loadingPengumuman.value = true
  try {
    const url = editModePengumuman.value ? `${API_BASE}?action=pengumuman&id=${pengumumanForm.value.id}` : `${API_BASE}?action=pengumuman`
    const res = await fetch(url, {
      method: editModePengumuman.value ? 'PUT' : 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(pengumumanForm.value)
    })
    const data = await res.json()
    if (data.success) { showPengumumanModal.value = false; fetchPengumuman() }
    else alert(data.message)
  } catch (e) { alert('Error saving') }
  finally { loadingPengumuman.value = false }
}

const togglePengumumanStatus = async (item) => {
  const newStatus = item.status === 'aktif' ? 'non-aktif' : 'aktif'
  try {
    await fetch(`${API_BASE}?action=pengumuman&id=${item.id}`, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ ...item, status: newStatus })
    })
    fetchPengumuman()
  } catch (e) { console.error(e) }
}

const confirmDeletePengumuman = (id) => { deletePengumumanId.value = id; showDeletePengumumanModal.value = true }

const deletePengumuman = async () => {
  try {
    const res = await fetch(`${API_BASE}?action=pengumuman&id=${deletePengumumanId.value}`, { method: 'DELETE' })
    if ((await res.json()).success) { showDeletePengumumanModal.value = false; fetchPengumuman() }
  } catch (e) { console.error(e) }
}

const formatDate = (d) => d ? new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) : '-'

// ============ GALERI FUNCTIONS ============
const fetchGaleri = async () => {
  loadingGaleri.value = true
  try {
    const res = await fetch(`${API_BASE}?action=galeri`)
    const data = await res.json()
    if (data.success) galeriList.value = data.data
  } catch (e) { console.error(e) }
  finally { loadingGaleri.value = false }
}

const openAddGaleri = () => {
  editModeGaleri.value = false
  galeriForm.value = { id: null, judul: '', caption: '', kategori: 'Kegiatan', file: null, previewUrl: '' }
  showGaleriModal.value = true
}

const openEditGaleri = (item) => {
  editModeGaleri.value = true
  galeriForm.value = { 
    id: item.id, 
    judul: item.judul, 
    caption: item.caption, 
    kategori: item.kategori,
    previewUrl: item.image_path, // Existing image
    file: null 
  }
  showGaleriModal.value = true
}

const handleGaleriFileSelect = (e) => {
  const file = e.target.files[0]
  if (!file) return
  galeriForm.value.file = file
  galeriForm.value.previewUrl = URL.createObjectURL(file)
}

const saveGaleri = async () => {
  if (!editModeGaleri.value && !galeriForm.value.file) return alert('Pilih foto terlebih dahulu')
  
  uploadingGaleri.value = true
  try {
    const fd = new FormData()
    if (galeriForm.value.file) fd.append('image', galeriForm.value.file)
    fd.append('judul', galeriForm.value.judul)
    fd.append('caption', galeriForm.value.caption)
    fd.append('kategori', galeriForm.value.kategori)
    
    // URL depends on mode
    let url = `${API_BASE}?action=galeri`
    let method = 'POST'
    
    if (editModeGaleri.value) {
      // For Edit, we usually use PUT/JSON, but if file is changed we might need POST/FormData or separate logic
      // Current API 'createGaleri' handles upload. 'updateGaleri' handles metadata (PUT).
      // If user changes file in Edit mode, complex. Let's assume Edit is for Metadata only given the current API structure.
      // If file replacement needed, easier to delete and re-upload.
      // So for Edit: use PUT with JSON
      url = `${API_BASE}?action=galeri&id=${galeriForm.value.id}`
      method = 'PUT'
      const data = {
        judul: galeriForm.value.judul,
        caption: galeriForm.value.caption,
        kategori: galeriForm.value.kategori,
        is_active: 1
      }
      
      const res = await fetch(url, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
      })
      const resData = await res.json()
      if (resData.success) { showGaleriModal.value = false; fetchGaleri() }
      else alert(resData.message)
      
    } else {
      // Create/Upload (POST)
      const res = await fetch(url, { method: 'POST', body: fd })
      const resData = await res.json()
      if (resData.success) { showGaleriModal.value = false; fetchGaleri() }
      else alert(resData.message)
    }
  } catch (e) {
    alert('Error saving gallery')
    console.error(e)
  } finally {
    uploadingGaleri.value = false
  }
}

const confirmDeleteGaleri = (id) => { deleteGaleriId.value = id; showDeleteGaleriModal.value = true }

const deleteGaleri = async () => {
  try {
    const res = await fetch(`${API_BASE}?action=galeri&id=${deleteGaleriId.value}`, { method: 'DELETE' })
    if ((await res.json()).success) { showDeleteGaleriModal.value = false; fetchGaleri() }
  } catch (e) { console.error(e) }
}

onMounted(() => { fetchBerita(); fetchPengumuman(); fetchGaleri() })
</script>

<template>
  <div>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
      <div>
        <h1 class="text-3xl font-bold text-gray-900 flex items-center gap-3">
          <NewspaperIcon class="w-8 h-8 text-primary" />
          Manajemen Konten
        </h1>
        <p class="text-gray-500 mt-1">Kelola berita dan pengumuman</p>
      </div>
    </div>

    <!-- Tabs -->
    <div class="flex gap-2 mb-6 bg-gray-100 p-1 rounded-xl w-fit">
      <button 
        @click="activeTab = 'berita'"
        class="px-6 py-2.5 rounded-lg font-medium transition-all flex items-center gap-2"
        :class="activeTab === 'berita' ? 'bg-white text-primary shadow-sm' : 'text-gray-600 hover:text-gray-900'"
      >
        <NewspaperIcon class="w-5 h-5" />
        Berita
      </button>
      <button 
        @click="activeTab = 'pengumuman'"
        class="px-6 py-2.5 rounded-lg font-medium transition-all flex items-center gap-2"
        :class="activeTab === 'pengumuman' ? 'bg-white text-amber-600 shadow-sm' : 'text-gray-600 hover:text-gray-900'"
      >
        <MegaphoneIcon class="w-5 h-5" />
        Pengumuman
      </button>
      <button 
        @click="activeTab = 'galeri'"
        class="px-6 py-2.5 rounded-lg font-medium transition-all flex items-center gap-2"
        :class="activeTab === 'galeri' ? 'bg-white text-purple-600 shadow-sm' : 'text-gray-600 hover:text-gray-900'"
      >
        <PhotoIconOutline class="w-5 h-5" />
        Galeri
      </button>
    </div>

    <!-- ============ BERITA TAB ============ -->
    <div v-show="activeTab === 'berita'">
      <div class="flex justify-end mb-6">
        <button @click="openAddBerita" class="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-xl font-medium hover:bg-teal-600 shadow-lg shadow-primary/20">
          <PlusIcon class="w-5 h-5" /> Tambah Berita
        </button>
      </div>

      <div v-if="loadingBerita && !beritaList.length" class="flex justify-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-4 border-primary border-t-transparent"></div>
      </div>

      <div v-else class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        <div v-for="b in beritaList" :key="b.id" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-lg transition-shadow">
          <div class="aspect-video bg-gray-100 relative overflow-hidden">
            <img v-if="b.foto_url" :src="b.foto_url" class="w-full h-full object-cover" />
            <div v-else class="w-full h-full flex items-center justify-center text-gray-400"><PhotoIcon class="w-16 h-16" /></div>
            <span class="absolute top-3 right-3 px-2 py-1 text-xs font-bold rounded-full" :class="b.status === 'published' ? 'bg-green-500 text-white' : 'bg-gray-400 text-white'">{{ b.status === 'published' ? 'Published' : 'Draft' }}</span>
          </div>
          <div class="p-5">
            <div class="text-xs text-gray-500 mb-2">{{ formatDate(b.tanggal_publish) }}</div>
            <h3 class="font-bold text-gray-900 mb-2 line-clamp-2">{{ b.judul }}</h3>
            <p class="text-sm text-gray-600 line-clamp-2 mb-4">{{ b.ringkasan || 'Tidak ada ringkasan' }}</p>
            <div class="flex gap-2">
              <button @click="openEditBerita(b)" class="flex-1 flex items-center justify-center gap-1 px-3 py-2 text-sm font-medium text-primary bg-primary/10 rounded-lg hover:bg-primary/20"><PencilIcon class="w-4 h-4" /> Edit</button>
              <button @click="confirmDeleteBerita(b.id)" class="px-3 py-2 text-sm text-red-500 bg-red-50 rounded-lg hover:bg-red-100"><TrashIcon class="w-4 h-4" /></button>
            </div>
          </div>
        </div>
        <div v-if="!beritaList.length && !loadingBerita" class="col-span-full text-center py-12">
          <NewspaperIcon class="w-16 h-16 text-gray-300 mx-auto mb-4" />
          <h3 class="text-lg font-medium text-gray-900 mb-2">Belum Ada Berita</h3>
          <button @click="openAddBerita" class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-xl"><PlusIcon class="w-5 h-5" /> Tambah Berita</button>
        </div>
      </div>
    </div>

    <!-- ============ PENGUMUMAN TAB ============ -->
    <div v-show="activeTab === 'pengumuman'">
      <div class="flex justify-end mb-6">
        <button @click="openAddPengumuman" class="flex items-center gap-2 px-4 py-2 bg-amber-500 text-white rounded-xl font-medium hover:bg-amber-600 shadow-lg shadow-amber-500/20">
          <PlusIcon class="w-5 h-5" /> Tambah Pengumuman
        </button>
      </div>

      <div v-if="loadingPengumuman && !pengumumanList.length" class="flex justify-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-4 border-amber-500 border-t-transparent"></div>
      </div>

      <div v-else class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full">
          <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
              <th class="text-left px-6 py-4 text-xs font-bold text-gray-500 uppercase">Teks Pengumuman</th>
              <th class="text-center px-6 py-4 text-xs font-bold text-gray-500 uppercase w-32">Status</th>
              <th class="text-center px-6 py-4 text-xs font-bold text-gray-500 uppercase w-32">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="p in pengumumanList" :key="p.id" class="hover:bg-gray-50">
              <td class="px-6 py-4"><p class="text-gray-900 font-medium">{{ p.teks }}</p></td>
              <td class="px-6 py-4 text-center">
                <button @click="togglePengumumanStatus(p)" class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold" :class="p.status === 'aktif' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'">
                  <CheckCircleIcon v-if="p.status === 'aktif'" class="w-4 h-4" /><XCircleIcon v-else class="w-4 h-4" />
                  {{ p.status === 'aktif' ? 'Aktif' : 'Non-aktif' }}
                </button>
              </td>
              <td class="px-6 py-4">
                <div class="flex items-center justify-center gap-2">
                  <button @click="openEditPengumuman(p)" class="p-2 text-primary bg-primary/10 rounded-lg hover:bg-primary/20"><PencilIcon class="w-4 h-4" /></button>
                  <button @click="confirmDeletePengumuman(p.id)" class="p-2 text-red-500 bg-red-50 rounded-lg hover:bg-red-100"><TrashIcon class="w-4 h-4" /></button>
                </div>
              </td>
            </tr>
            <tr v-if="!pengumumanList.length && !loadingPengumuman">
              <td colspan="3" class="px-6 py-12 text-center">
                <MegaphoneIcon class="w-16 h-16 text-gray-300 mx-auto mb-4" />
                <h3 class="text-lg font-medium text-gray-900 mb-2">Belum Ada Pengumuman</h3>
                <button @click="openAddPengumuman" class="inline-flex items-center gap-2 px-4 py-2 bg-amber-500 text-white rounded-xl"><PlusIcon class="w-5 h-5" /> Tambah</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ============ GALERI TAB ============ -->
    <div v-show="activeTab === 'galeri'">
      <div class="flex justify-end mb-6">
        <button @click="openAddGaleri" class="flex items-center gap-2 px-4 py-2 bg-purple-600 text-white rounded-xl font-medium hover:bg-purple-700 shadow-lg shadow-purple-600/20">
          <CloudArrowUpIcon class="w-5 h-5" /> Upload Foto
        </button>
      </div>

      <div v-if="loadingGaleri && !galeriList.length" class="flex justify-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-4 border-purple-600 border-t-transparent"></div>
      </div>

      <div v-else class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        <div v-for="item in galeriList" :key="item.id" class="group relative bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-lg transition-all">
          <div class="aspect-square bg-gray-100 relative overflow-hidden">
            <img :src="item.image_path" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" />
            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
              <button @click="openEditGaleri(item)" class="p-2 bg-white/20 backdrop-blur-sm text-white rounded-full hover:bg-white/40"><PencilIcon class="w-5 h-5" /></button>
              <button @click="confirmDeleteGaleri(item.id)" class="p-2 bg-red-500/80 backdrop-blur-sm text-white rounded-full hover:bg-red-600"><TrashIcon class="w-5 h-5" /></button>
            </div>
          </div>
          <div class="p-3">
            <h3 class="font-bold text-gray-900 text-sm truncate">{{ item.judul || 'Tanpa Judul' }}</h3>
            <p class="text-xs text-gray-500 truncate">{{ item.kategori }}</p>
          </div>
        </div>
        
        <!-- Upload Placeholder if empty -->
        <div v-if="!galeriList.length && !loadingGaleri" class="col-span-full border-2 border-dashed border-gray-300 rounded-3xl p-12 text-center hover:border-purple-500 hover:bg-purple-50 transition-colors cursor-pointer" @click="openAddGaleri">
          <CloudArrowUpIcon class="w-16 h-16 text-gray-300 mx-auto mb-4" />
          <h3 class="text-lg font-medium text-gray-900 mb-1">Upload Foto Pertama</h3>
          <p class="text-gray-500 text-sm">Klik di sini untuk menambahkan foto kegiatan</p>
        </div>
      </div>
    </div>

    <!-- ============ BERITA MODAL ============ -->
    <div v-if="showBeritaModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4" @click.self="showBeritaModal = false">
      <div class="bg-white rounded-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto shadow-2xl">
        <div class="flex items-center justify-between p-6 border-b"><h2 class="text-xl font-bold">{{ editModeBerita ? 'Edit' : 'Tambah' }} Berita</h2><button @click="showBeritaModal = false"><XMarkIcon class="w-6 h-6 text-gray-400" /></button></div>
        <div class="p-6 space-y-5">
          <div><label class="block text-sm font-medium text-gray-700 mb-2">Judul *</label><input v-model="beritaForm.judul" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none" placeholder="Judul berita" /></div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Foto</label>
            <div class="flex gap-4 items-start">
              <div class="w-32 h-20 bg-gray-100 rounded-lg overflow-hidden"><img v-if="beritaForm.foto_url" :src="beritaForm.foto_url" class="w-full h-full object-cover" /><div v-else class="w-full h-full flex items-center justify-center text-gray-400"><PhotoIcon class="w-8 h-8" /></div></div>
              <div class="flex-1"><input type="file" accept="image/*" @change="handleFotoUpload" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-primary/10 file:text-primary" /><p v-if="uploadingFoto" class="text-xs text-primary mt-1">Mengupload...</p></div>
            </div>
          </div>
          <div><label class="block text-sm font-medium text-gray-700 mb-2">Ringkasan</label><textarea v-model="beritaForm.ringkasan" rows="2" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none" placeholder="Ringkasan"></textarea></div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Isi Berita</label>
            <div class="prose max-w-none">
              <textarea v-model="beritaForm.isi_berita" rows="10" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none font-sans" placeholder="Tulis isi berita di sini..."></textarea>
            </div>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div><label class="block text-sm font-medium text-gray-700 mb-2">Tanggal</label><input v-model="beritaForm.tanggal_publish" type="date" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none" /></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-2">Status</label><select v-model="beritaForm.status" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none"><option value="published">Published</option><option value="draft">Draft</option></select></div>
          </div>
        </div>
        <div class="flex gap-3 p-6 border-t"><button @click="showBeritaModal = false" class="flex-1 py-3 px-4 bg-gray-100 rounded-xl font-medium hover:bg-gray-200">Batal</button><button @click="saveBerita" :disabled="loadingBerita" class="flex-1 py-3 px-4 bg-primary text-white rounded-xl font-medium hover:bg-teal-600 disabled:opacity-50">{{ loadingBerita ? 'Menyimpan...' : 'Simpan' }}</button></div>
      </div>
    </div>

    <!-- ============ PENGUMUMAN MODAL ============ -->
    <div v-if="showPengumumanModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4" @click.self="showPengumumanModal = false">
      <div class="bg-white rounded-2xl w-full max-w-lg shadow-2xl">
        <div class="flex items-center justify-between p-6 border-b"><h2 class="text-xl font-bold">{{ editModePengumuman ? 'Edit' : 'Tambah' }} Pengumuman</h2><button @click="showPengumumanModal = false"><XMarkIcon class="w-6 h-6 text-gray-400" /></button></div>
        <div class="p-6 space-y-5">
          <div><label class="block text-sm font-medium text-gray-700 mb-2">Teks *</label><textarea v-model="pengumumanForm.teks" rows="3" maxlength="500" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 outline-none" placeholder="Teks pengumuman"></textarea><p class="text-xs text-gray-500 mt-1">{{ pengumumanForm.teks.length }}/500</p></div>
          <div><label class="block text-sm font-medium text-gray-700 mb-2">Status</label><select v-model="pengumumanForm.status" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 outline-none"><option value="aktif">Aktif</option><option value="non-aktif">Non-aktif</option></select></div>
        </div>
        <div class="flex gap-3 p-6 border-t"><button @click="showPengumumanModal = false" class="flex-1 py-3 px-4 bg-gray-100 rounded-xl font-medium hover:bg-gray-200">Batal</button><button @click="savePengumuman" :disabled="loadingPengumuman" class="flex-1 py-3 px-4 bg-amber-500 text-white rounded-xl font-medium hover:bg-amber-600 disabled:opacity-50">{{ loadingPengumuman ? 'Menyimpan...' : 'Simpan' }}</button></div>
      </div>
    </div>

    <!-- Delete Modals -->
    <div v-if="showDeleteBeritaModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
      <div class="bg-white rounded-2xl w-full max-w-md p-6 shadow-2xl text-center">
        <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4"><TrashIcon class="w-8 h-8 text-red-500" /></div>
        <h3 class="text-xl font-bold mb-2">Hapus Berita?</h3><p class="text-gray-500 mb-6">Data tidak dapat dikembalikan.</p>
        <div class="flex gap-3"><button @click="showDeleteBeritaModal = false" class="flex-1 py-3 px-4 bg-gray-100 rounded-xl font-medium">Batal</button><button @click="deleteBerita" class="flex-1 py-3 px-4 bg-red-500 text-white rounded-xl font-medium">Hapus</button></div>
      </div>
    </div>
    <div v-if="showDeletePengumumanModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
      <div class="bg-white rounded-2xl w-full max-w-md p-6 shadow-2xl text-center">
        <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4"><TrashIcon class="w-8 h-8 text-red-500" /></div>
        <h3 class="text-xl font-bold mb-2">Hapus Pengumuman?</h3><p class="text-gray-500 mb-6">Data tidak dapat dikembalikan.</p>
        <div class="flex gap-3"><button @click="showDeletePengumumanModal = false" class="flex-1 py-3 px-4 bg-gray-100 rounded-xl font-medium">Batal</button><button @click="deletePengumuman" class="flex-1 py-3 px-4 bg-red-500 text-white rounded-xl font-medium">Hapus</button></div>
      </div>
    </div>

    
    <!-- ============ GALERI MODAL ============ -->
    <div v-if="showGaleriModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4" @click.self="showGaleriModal = false">
      <div class="bg-white rounded-2xl w-full max-w-lg shadow-2xl">
        <div class="flex items-center justify-between p-6 border-b">
          <h2 class="text-xl font-bold">{{ editModeGaleri ? 'Edit' : 'Upload' }} Foto</h2>
          <button @click="showGaleriModal = false"><XMarkIcon class="w-6 h-6 text-gray-400" /></button>
        </div>
        <div class="p-6 space-y-5">
          <!-- Image Preview / Upload Area -->
          <div v-if="!editModeGaleri" class="w-full aspect-video bg-gray-50 border-2 border-dashed border-gray-300 rounded-xl flex flex-col items-center justify-center overflow-hidden relative cursor-pointer hover:border-purple-500 hover:bg-purple-50 transition-colors">
            <input type="file" accept="image/*" @change="handleGaleriFileSelect" class="absolute inset-0 opacity-0 cursor-pointer" />
            <img v-if="galeriForm.previewUrl" :src="galeriForm.previewUrl" class="w-full h-full object-contain" />
            <div v-else class="text-center p-6">
              <CloudArrowUpIcon class="w-10 h-10 text-gray-400 mx-auto mb-2" />
              <div class="text-sm font-medium text-gray-600">Klik atau Drag & Drop foto di sini</div>
              <div class="text-xs text-gray-400 mt-1">JPG, PNG, WebP</div>
            </div>
          </div>
          <div v-else class="w-full aspect-video bg-gray-100 rounded-xl overflow-hidden">
             <img :src="galeriForm.previewUrl" class="w-full h-full object-contain" />
          </div>

          <!-- Metadata Fields -->
          <div><label class="block text-sm font-medium text-gray-700 mb-2">Judul Kegiatan</label><input v-model="galeriForm.judul" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 outline-none" placeholder="Contoh: Perbaikan Pipa RT 03" /></div>
          
          <div class="grid grid-cols-2 gap-4">
             <div><label class="block text-sm font-medium text-gray-700 mb-2">Kategori</label><select v-model="galeriForm.kategori" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 outline-none"><option>Kegiatan</option><option>Perbaikan</option><option>Rapat</option><option>Sosialisasi</option><option>Lainnya</option></select></div>
          </div>

          <div><label class="block text-sm font-medium text-gray-700 mb-2">Keterangan (Caption)</label><textarea v-model="galeriForm.caption" rows="2" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 outline-none" placeholder="Deskripsi singkat kegiatan..."></textarea></div>
        </div>
        <div class="flex gap-3 p-6 border-t">
          <button @click="showGaleriModal = false" class="flex-1 py-3 px-4 bg-gray-100 rounded-xl font-medium hover:bg-gray-200">Batal</button>
          <button @click="saveGaleri" :disabled="uploadingGaleri" class="flex-1 py-3 px-4 bg-purple-600 text-white rounded-xl font-medium hover:bg-purple-700 disabled:opacity-50 flex justify-center items-center gap-2">
            <svg v-if="uploadingGaleri" class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
            {{ uploadingGaleri ? 'Menyimpan...' : 'Simpan' }}
          </button>
        </div>
      </div>
    </div>
    
    <div v-if="showDeleteGaleriModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
      <div class="bg-white rounded-2xl w-full max-w-md p-6 shadow-2xl text-center">
        <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4"><TrashIcon class="w-8 h-8 text-red-500" /></div>
        <h3 class="text-xl font-bold mb-2">Hapus Foto?</h3><p class="text-gray-500 mb-6">Foto akan dihapus permanen dari galeri.</p>
        <div class="flex gap-3"><button @click="showDeleteGaleriModal = false" class="flex-1 py-3 px-4 bg-gray-100 rounded-xl font-medium">Batal</button><button @click="deleteGaleri" class="flex-1 py-3 px-4 bg-red-500 text-white rounded-xl font-medium">Hapus</button></div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
</style>
