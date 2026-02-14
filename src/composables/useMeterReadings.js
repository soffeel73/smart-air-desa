import { ref } from 'vue'

// Shared state for meter readings
const meterReadings = ref([])
const isLoading = ref(false)
const lastFetch = ref(null)

// API configuration
const API_BASE = '/api/input_meter.php'

/**
 * Composable for managing meter readings
 * Provides shared state and methods for both admin and petugas components
 */
export function useMeterReadings() {
  
  /**
   * Fetch meter readings from API
   * @param {Object} filters - Optional filters (year, month, customer_id)
   */
  const fetchReadings = async (filters = {}) => {
    isLoading.value = true
    try {
      // Build query string
      const params = new URLSearchParams()
      if (filters.year) params.append('year', filters.year)
      if (filters.month) params.append('month', filters.month)
      if (filters.customer_id) params.append('customer_id', filters.customer_id)
      
      const url = params.toString() ? `${API_BASE}?${params}` : API_BASE
      const response = await fetch(url)
      const data = await response.json()
      
      if (data.success) {
        meterReadings.value = data.data
        lastFetch.value = new Date()
        return { success: true, data: data.data }
      } else {
        return { success: false, message: data.message || 'Gagal memuat data' }
      }
    } catch (error) {
      console.error('Error fetching meter readings:', error)
      return { success: false, message: 'Gagal memuat data pembacaan meter' }
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Save meter reading (petugas input)
   * @param {Object} data - Meter reading data
   */
  const saveMeterReading = async (data) => {
    isLoading.value = true
    try {
      const response = await fetch(API_BASE, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
      })
      const result = await response.json()
      
      if (result.success) {
        // Add new reading to local state
        if (result.data) {
          meterReadings.value.unshift(result.data)
        }
        return { success: true, data: result.data, message: result.message }
      } else {
        return { success: false, message: result.message || 'Gagal menyimpan data' }
      }
    } catch (error) {
      console.error('Error saving meter reading:', error)
      return { success: false, message: 'Gagal menyimpan data pembacaan meter' }
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Update meter reading
   * @param {Number} id - Reading ID
   * @param {Object} data - Updated data
   */
  const updateMeterReading = async (id, data) => {
    isLoading.value = true
    try {
      const response = await fetch(`${API_BASE}?id=${id}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
      })
      const result = await response.json()
      
      if (result.success) {
        // Update local state
        const index = meterReadings.value.findIndex(r => r.id === id)
        if (index !== -1 && result.data) {
          meterReadings.value[index] = result.data
        }
        return { success: true, data: result.data }
      } else {
        return { success: false, message: result.message || 'Gagal memperbarui data' }
      }
    } catch (error) {
      console.error('Error updating meter reading:', error)
      return { success: false, message: 'Gagal memperbarui data' }
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Delete meter reading
   * @param {Number} id - Reading ID
   */
  const deleteMeterReading = async (id) => {
    isLoading.value = true
    try {
      const response = await fetch(`${API_BASE}?id=${id}`, {
        method: 'DELETE'
      })
      const result = await response.json()
      
      if (result.success) {
        // Remove from local state
        meterReadings.value = meterReadings.value.filter(r => r.id !== id)
        return { success: true }
      } else {
        return { success: false, message: result.message || 'Gagal menghapus data' }
      }
    } catch (error) {
      console.error('Error deleting meter reading:', error)
      return { success: false, message: 'Gagal menghapus data' }
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Refresh data if stale
   */
  const refreshIfStale = async (maxAgeSeconds = 30) => {
    const now = new Date()
    if (!lastFetch.value || (now - lastFetch.value) / 1000 > maxAgeSeconds) {
      await fetchReadings()
    }
  }

  return {
    // State
    meterReadings,
    isLoading,
    lastFetch,
    
    // Methods
    fetchReadings,
    saveMeterReading,
    updateMeterReading,
    deleteMeterReading,
    refreshIfStale
  }
}
