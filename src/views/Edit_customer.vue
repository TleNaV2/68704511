<template>
  <div class="container mt-4 col-md-4 bg-body-secondary rounded p-4">
    <h2 class="text-center mb-3">แก้ไขข้อมูลลูกค้า</h2>

    <div v-if="loading" class="text-center">กำลังโหลดข้อมูล...</div>

    <form v-else @submit.prevent="saveData">
      <div class="mb-2">
        <input v-model="form.firstName" class="form-control" placeholder="ชื่อ" required />
      </div>
      <div class="mb-2">
        <input v-model="form.lastName" class="form-control" placeholder="นามสกุล" required />
      </div>
      <div class="mb-2">
        <input v-model="form.phone" class="form-control" placeholder="เบอร์โทร" required />
      </div>
      <div class="mb-2">
        <input v-model="form.username" class="form-control" placeholder="ชื่อผู้ใช้" required />
      </div>
      <div class="mb-2">
        <input
          type="password"
          v-model="form.password"
          class="form-control"
          placeholder="รหัสผ่านใหม่ (เว้นว่างไว้ถ้าไม่ต้องการเปลี่ยน)"
        />
      </div>
      <div class="text-center mt-4">
        <button type="submit" class="btn btn-primary mb-3">บันทึก</button>
        <router-link to="/customer" class="btn btn-secondary mb-3 ms-2">ยกเลิก</router-link>
      </div>
    </form>

    <div v-if="message" class="alert alert-info mt-3">{{ message }}</div>
  </div>
</template>

<script>
import { API_BASE } from "../config";

export default {
  name: "EditCustomer",
  data() {
    return {
      form: { customer_id: null, firstName: "", lastName: "", phone: "", username: "", password: "" },
      loading: true,
      message: ""
    };
  },
  async mounted() {
    try {
      const res = await fetch(`${API_BASE}/show_customer.php?id=${this.$route.params.id}`);
      const result = await res.json();

      if (!result.success) {
        throw new Error(result.message || "ไม่พบข้อมูล");
      }
      this.form = { ...result.data, password: "" };
    } catch (err) {
      this.message = "เกิดข้อผิดพลาด: " + err.message;
    } finally {
      this.loading = false;
    }
  },
  methods: {
    async saveData() {
      try {
        const res = await fetch(`${API_BASE}/update_customer.php`, {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(this.form)
        });
        const data = await res.json();
        this.message = data.message;

        if (data.success) {
          this.$router.push("/customer");
        }
      } catch (err) {
        this.message = "เกิดข้อผิดพลาด: " + err.message;
      }
    }
  }
};
</script>
