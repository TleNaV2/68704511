<template>
  <div class="container mt-4 col-md-5 bg-body-secondary rounded p-4">
    <h2 class="text-center mb-3">เพิ่มข้อมูลติดต่อเรา</h2>

    <form @submit.prevent="addData">
      <div class="mb-3">
        <label for="contact-subject" class="form-label">หัวข้อ</label>
        <input id="contact-subject" v-model.trim="contact.subject" class="form-control" placeholder="หัวข้อ" required />
      </div>

      <div class="mb-3">
        <label for="contact-detail" class="form-label">รายละเอียด</label>
        <textarea id="contact-detail" v-model.trim="contact.detail" class="form-control" rows="4" placeholder="รายละเอียด" required></textarea>
      </div>

      <div class="mb-3">
        <label for="contact-fullname" class="form-label">ชื่อ-นามสกุล</label>
        <input id="contact-fullname" v-model.trim="contact.fullname" class="form-control" placeholder="ชื่อ-นามสกุล" required />
      </div>

      <div class="mb-3">
        <label for="contact-email" class="form-label">อีเมล</label>
        <input id="contact-email" v-model.trim="contact.email" type="email" class="form-control" placeholder="E-mail" required />
      </div>

      <div class="text-center mt-4">
        <button type="submit" class="btn btn-primary mb-3" :disabled="isSubmitting">
          {{ isSubmitting ? "กำลังบันทึก..." : "บันทึก" }}
        </button>
        <router-link to="/contact" class="btn btn-secondary mb-3 ms-2">ยกเลิก</router-link>
      </div>
    </form>

    <div v-if="message" class="alert alert-danger mt-3" role="alert">
      {{ message }}
    </div>
  </div>
</template>

<script>
import { API_BASE } from "../config";

export default {
  data() {
    return {
      contact: {
        subject: "",
        detail: "",
        fullname: "",
        email: ""
      },
      message: "",
      isSubmitting: false
    }
  },
  methods: {
    async addData() {
      if (this.isSubmitting) return

      this.isSubmitting = true
      this.message = ""

      try {
        const res = await fetch(`${API_BASE}/add_contact.php`, {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(this.contact)
        })

        if (!res.ok) {
          throw new Error("ไม่สามารถบันทึกข้อมูลติดต่อได้")
        }

        const data = await res.json()

        if (!data.success) {
          throw new Error(data.message || "เพิ่มข้อมูลติดต่อไม่สำเร็จ")
        }

        await this.$router.push("/contact")
      } catch (err) {
        this.message = "เกิดข้อผิดพลาด: " + err.message
      } finally {
        this.isSubmitting = false
      }
    }
  }
}
</script>
