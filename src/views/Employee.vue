<template>
  <div class="container page">
    <div class="header-row">
      <div>
        <h1>ข้อมูลพนักงาน</h1>
        <p class="lead">รายชื่อพนักงานทั้งหมด</p>
      </div>
      <router-link to="/add-employee" class="btn btn-primary">เพิ่มพนักงาน</router-link>
    </div>

    <table class="employee-table">
      <thead>
        <tr>
          <th>ลำดับที่</th>
          <th>รหัสพนักงาน</th>
          <th>ชื่อ</th>
          <th>นามสกุล</th>
          <th>เบอร์โทร</th>
          <th>ชื่อผู้ใช้</th>
          <th>จัดการ</th>
        </tr>
      </thead>

      <tbody>
        <tr v-for="(employee, index) in employees" :key="employee.emp_id">
          <td>{{ index + 1 }}</td>
          <td>{{ employee.emp_id }}</td>
          <td>{{ employee.firstName }}</td>
          <td>{{ employee.lastName }}</td>
          <td>{{ employee.phone }}</td>
          <td>{{ employee.username }}</td>
          <td>
            <router-link :to="`/edit-employee/${employee.emp_id}`" class="btn btn-warning btn-sm me-1">แก้ไข</router-link>
            <button class="btn btn-danger btn-sm" @click="deleteEmployee(employee)">ลบ</button>
          </td>
        </tr>
      </tbody>
    </table>

    <div v-if="loading" class="text-center">
      <p>กำลังโหลดข้อมูล...</p>
    </div>

    <div v-if="error" class="alert alert-danger">
      {{ error }}
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import { API_BASE } from '../config'

export default {
  name: 'EmployeeTable',

  setup () {
    const employees = ref([])
    const loading = ref(true)
    const error = ref(null)

    const fetchEmployees = async () => {
      try {
        const response = await fetch(`${API_BASE}/show_employee.php`)

        if (!response.ok) {
          throw new Error('ไม่สามารถดึงข้อมูลพนักงานได้')
        }

        const result = await response.json()

        if (!result.success || !Array.isArray(result.data)) {
          throw new Error(result.message || 'รูปแบบข้อมูลพนักงานไม่ถูกต้อง')
        }

        employees.value = result.data
      } catch (err) {
        error.value = err.message
      } finally {
        loading.value = false
      }
    }

    const deleteEmployee = async (employee) => {
      if (!window.confirm(`ต้องการลบพนักงาน ${employee.firstName} ${employee.lastName} ใช่หรือไม่?`)) return

      try {
        const response = await fetch(`${API_BASE}/delete_employee.php`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ emp_id: employee.emp_id })
        })
        const result = await response.json()

        if (!result.success) {
          throw new Error(result.message || 'ลบข้อมูลไม่สำเร็จ')
        }
        employees.value = employees.value.filter((e) => e.emp_id !== employee.emp_id)
      } catch (err) {
        error.value = err.message
      }
    }

    onMounted(() => {
      fetchEmployees()
    })

    return {
      employees,
      loading,
      error,
      deleteEmployee
    }
  }
}
</script>

<style scoped>
.page { margin: 0 auto; max-width: 1120px; padding: 40px 24px 60px; }
.page h1 { color: #173d3b; }
.lead { color: #6c757d; margin-bottom: 24px; }
.header-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}
.btn {
  border-radius: 10px;
}
.employee-table { background: #fff; border-collapse: collapse; width: 100%; }
.employee-table th, .employee-table td { border: 1px solid #dee2e6; padding: 12px; text-align: left; }
.employee-table th { background: #212529; color: #fff; }
.text-center { text-align: center; }
.alert { margin-top: 16px; }
</style>
