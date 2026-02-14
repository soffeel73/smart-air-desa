import { ref, computed } from 'vue'

// Shared state for customer data
const customers = ref([])
const isLoading = ref(false)
const lastFetch = ref(null)

// API configuration
const API_BASE = '/api/pelanggan.php'

/**
 * Composable for managing customer data
 * Provides shared state and methods for both admin and petugas components
 */
export function useCustomers() {
  
  /**
   * Fetch customers from API
   */
  const fetchCustomers = async () => {
    isLoading.value = true
    try {
      const response = await fetch(API_BASE)
      const data = await response.json()
      
      if (data.success) {
        console.log('API Response:', data.data) // Debug: Check raw API data
        customers.value = data.data
        console.log('Mapped customers:', customers.value) // Debug: Check mapped data
        lastFetch.value = new Date()
        return { success: true, data: data.data }
      } else {
        return { success: false, message: 'Gagal memuat data pelanggan' }
      }
    } catch (error) {
      console.error('Error fetching customers:', error)
      return { success: false, message: 'Gagal memuat data pelanggan' }
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Group customers by address for petugas interface
   */
  const customersByAddress = computed(() => {
    const grouped = {}
    
    customers.value.forEach(customer => {
      const address = customer.address || 'Lainnya'
      if (!grouped[address]) {
        grouped[address] = []
      }
      
      // Debug: Check mapped values
      const mappedCustomer = {
        id: customer.id,
        customer_id: customer.customer_id,
        nama: customer.name,
        alamat: customer.address,
        golongan: customer.type,
        phone: customer.phone,
        meter_terakhir: customer.last_meter_reading || 0,
        bulan_terakhir: customer.last_reading_month || '2024-01',
        sudah_input_bulan_ini: customer.has_current_month_reading || false
      }
      
      console.log(`Customer ${customer.customer_id}: last_meter_reading =`, customer.last_meter_reading, '→ meter_terakhir =', mappedCustomer.meter_terakhir)
      
      grouped[address].push(mappedCustomer)
    })
    
    return grouped
  })

  /**
   * Get unique addresses
   */
  const uniqueAddresses = computed(() => {
    return Object.keys(customersByAddress.value).sort()
  })

  /**
   * Get customer by ID
   */
  const getCustomerById = (customerId) => {
    return customers.value.find(c => c.customer_id === customerId)
  }

  /**
   * Add new customer
   */
  const addCustomer = async (customerData) => {
    isLoading.value = true
    try {
      const response = await fetch(API_BASE, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(customerData)
      })
      const data = await response.json()
      
      if (data.success) {
        customers.value.push(data.data)
        return { success: true, data: data.data }
      } else {
        return { success: false, message: data.message || 'Gagal menyimpan pelanggan' }
      }
    } catch (error) {
      console.error('Error adding customer:', error)
      return { success: false, message: 'Gagal menyimpan pelanggan' }
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Update customer
   */
  const updateCustomer = async (customerId, customerData) => {
    isLoading.value = true
    try {
      const response = await fetch(`${API_BASE}?id=${customerId}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(customerData)
      })
      const data = await response.json()
      
      if (data.success) {
        const index = customers.value.findIndex(c => c.id === customerId)
        if (index !== -1) {
          customers.value[index] = data.data
        }
        return { success: true, data: data.data }
      } else {
        return { success: false, message: data.message || 'Gagal memperbarui pelanggan' }
      }
    } catch (error) {
      console.error('Error updating customer:', error)
      return { success: false, message: 'Gagal memperbarui pelanggan' }
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Delete customer
   */
  const deleteCustomer = async (customerId) => {
    isLoading.value = true
    try {
      const response = await fetch(`${API_BASE}?id=${customerId}`, {
        method: 'DELETE'
      })
      const data = await response.json()
      
      if (data.success) {
        customers.value = customers.value.filter(c => c.id !== customerId)
        return { success: true }
      } else {
        return { success: false, message: data.message || 'Gagal menghapus pelanggan' }
      }
    } catch (error) {
      console.error('Error deleting customer:', error)
      return { success: false, message: 'Gagal menghapus pelanggan' }
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Mark customer as having input for current month
   */
  const markCustomerInputted = (customerId) => {
    const customer = customers.value.find(c => c.customer_id === customerId)
    if (customer) {
      customer.has_current_month_reading = true
    }
  }

  /**
   * Refresh data if stale (older than 30 seconds)
   */
  const refreshIfStale = async () => {
    const now = new Date()
    if (!lastFetch.value || (now - lastFetch.value) > 30000) {
      await fetchCustomers()
    }
  }

  return {
    // State
    customers,
    customersByAddress,
    uniqueAddresses,
    isLoading,
    lastFetch,
    
    // Methods
    fetchCustomers,
    getCustomerById,
    addCustomer,
    updateCustomer,
    deleteCustomer,
    markCustomerInputted,
    refreshIfStale
  }
}
