<template>
  <div class="container mt-4 col-md-5 bg-body-secondary rounded p-4">
    <h2 class="text-center mb-3">แก้ไขข้อมูลติดต่อเรา</h2>

    <div v-if="loading" class="text-center">กำลังโหลดข้อมูล...</div>

    <form v-else @submit.prevent="saveData">
      <div class="mb-3">
        <label class="form-label">หัวข้อ</label>
        <input v-model="form.subject" class="form-control" placeholder="หัวข้อ" required />
      </div>

      <div class="mb-3">
        <label class="form-label">รายละเอียด</label>
        <textarea v-model="form.detail" class="form-control" rows="4" placeholder="รายละเอียด" required></textarea>
      </div>

      <div class="mb-3">
        <label class="form-label">ชื่อ-นามสกุล</label>
        <input v-model="form.fullname" class="form-control" placeholder="ชื่อ-นามสกุล" required />
      </div>

      <div class="mb-3">
        <label class="form-label">อีเมล</label>
        <input v-model="form.email" type="email" class="form-control" placeholder="E-mail" required />
      </div>

      <div class="text-center mt-4">
        <button type="submit" class="btn btn-primary mb-3">บันทึก</button>
        <router-link to="/contact" class="btn btn-secondary mb-3 ms-2">ยกเลิก</router-link>
      </div>
    </form>

    <div v-if="message" class="alert alert-info mt-3">{{ message }}</div>
  </div>
</template>

<script>
import { API_BASE } from "../config";

export default {
  name: "EditContact",
  data() {
    return {
      form: { contact_id: null, subject: "", detail: "", fullname: "", email: "" },
      loading: true,
      message: ""
    };
  },
  async mounted() {
    try {
      const res = await fetch(`${API_BASE}/show_contact.php?id=${this.$route.params.id}`);
      const result = await res.json();

      if (!result.success) {
        throw new Error(result.message || "ไม่พบข้อมูล");
      }
      this.form = { ...result.data };
    } catch (err) {
      this.message = "เกิดข้อผิดพลาด: " + err.message;
    } finally {
      this.loading = false;
    }
  },
  methods: {
    async saveData() {
      try {
        const res = await fetch(`${API_BASE}/update_contact.php`, {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(this.form)
        });
        const data = await res.json();
        this.message = data.message;

        if (data.success) {
          this.$router.push("/contact");
        }
      } catch (err) {
        this.message = "เกิดข้อผิดพลาด: " + err.message;
      }
    }
  }
};
</script>
