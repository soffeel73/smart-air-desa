<script setup>
import Header from '../components/Header.vue'
import AnnouncementMarquee from '../components/AnnouncementMarquee.vue'
import Hero from '../components/Hero.vue'
import Stats from '../components/Stats.vue'
import Features from '../components/Features.vue'
import TarifSection from '../components/TarifSection.vue'
import NewsSection from '../components/NewsSection.vue'
import OfficerPortal from '../components/OfficerPortal.vue'
import Footer from '../components/Footer.vue'
import { useRouter } from 'vue-router'

const router = useRouter()

const handleLogin = () => {
  router.push('/login')
}

const handleCheckBill = (billData) => {
  // Simpan data tagihan ke localStorage untuk diakses oleh BillResult
  localStorage.setItem('bill_data', JSON.stringify(billData))
  router.push('/bill-result')
}
</script>

<template>
  <div>
    <Header @login="handleLogin" />
    <Hero @check-bill="handleCheckBill" />
    <AnnouncementMarquee />
    <Stats />
    <Features />
    <TarifSection />
    <NewsSection />
    <OfficerPortal />
    <Footer />
  </div>
</template>

