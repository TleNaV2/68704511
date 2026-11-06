<template>
  <main class="container my-4">
    <h1 class="h2 mb-4">จัดการสินค้า</h1>

    <div v-if="message" class="alert" :class="messageType" role="status">{{ message }}</div>
    <div v-if="error || imageError" class="alert alert-danger" role="alert">{{ error || imageError }}</div>

    <form class="card card-body mb-4" @submit.prevent="saveProduct">
      <h2 class="h5">{{ editingId ? "แก้ไขสินค้า" : "เพิ่มสินค้า" }}</h2>
      <div class="row g-3">
        <div class="col-md-6">
          <label for="product-name" class="form-label">ชื่อสินค้า</label>
          <input id="product-name" v-model.trim="form.product_name" class="form-control" required>
        </div>
        <div class="col-md-6">
          <label for="product-price" class="form-label">ราคา</label>
          <input id="product-price" v-model="form.price" type="number" min="0" step="0.01" class="form-control" required>
        </div>
        <div class="col-md-6">
          <label for="product-stock" class="form-label">จำนวนสินค้า</label>
          <input id="product-stock" v-model="form.stock" type="number" min="0" step="1" class="form-control" required>
        </div>
        <div class="col-md-6">
          <label for="product-image" class="form-label">รูปสินค้า</label>
          <input
            id="product-image"
            ref="imageInput"
            type="file"
            accept="image/jpeg,image/png,image/gif,image/webp"
            class="form-control"
            @change="selectImage"
          >
          <div class="form-text">รองรับ JPEG, PNG, GIF และ WebP ขนาดไม่เกิน 5 MB</div>
          <img
            v-if="imagePreview || (editingId && form.image)"
            :src="imagePreview || imageUrl(form.image)"
            alt="ตัวอย่างรูปสินค้า"
            class="product-preview mt-2"
          >
        </div>
        <div class="col-12">
          <label for="product-description" class="form-label">รายละเอียด</label>
          <textarea id="product-description" v-model.trim="form.description" class="form-control" rows="3"></textarea>
        </div>
        <div class="col-12">
          <button type="submit" class="btn btn-primary me-2" :disabled="saving">
            {{ saving ? "กำลังบันทึก..." : editingId ? "บันทึกการแก้ไข" : "เพิ่มสินค้า" }}
          </button>
          <button v-if="editingId" type="button" class="btn btn-secondary" @click="resetForm">ยกเลิก</button>
        </div>
      </div>
    </form>

    <div v-if="loading" class="text-center py-4">กำลังโหลดข้อมูลสินค้า...</div>
    <div v-else-if="products.length === 0" class="alert alert-info text-center">ยังไม่มีสินค้า</div>
    <div v-else class="table-responsive">
      <table class="table table-bordered table-striped align-middle">
        <thead class="table-light">
          <tr>
            <th>รูปภาพ</th>
            <th>ชื่อสินค้า</th>
            <th>รายละเอียด</th>
            <th>ราคา</th>
            <th>จำนวน</th>
            <th>จัดการ</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="product in products" :key="product.product_id">
            <td>
              <img
                v-if="product.image"
                :src="imageUrl(product.image)"
                :alt="product.product_name"
                class="product-thumbnail"
                @error="hideBrokenImage"
              >
              <span v-else>-</span>
            </td>
            <td>{{ product.product_name }}</td>
            <td>{{ product.description || "-" }}</td>
            <td>{{ product.price }} บาท</td>
            <td>{{ product.stock }}</td>
            <td class="text-nowrap">
              <button type="button" class="btn btn-warning btn-sm me-2" @click="editProduct(product)">แก้ไข</button>
              <button type="button" class="btn btn-danger btn-sm" :disabled="saving" @click="deleteProduct(product)">ลบ</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </main>
</template>

<script>
import { onBeforeUnmount, onMounted, reactive, ref } from "vue";
import { API_BASE } from "@/config";

export default {
  name: "ProductManage",
  setup() {
    const apiBase = API_BASE.replace(/\/$/, "");
    const apiUrl = `${apiBase}/api_product.php`;
    const products = ref([]);
    const loading = ref(true);
    const saving = ref(false);
    const editingId = ref(null);
    const error = ref("");
    const imageError = ref("");
    const message = ref("");
    const messageType = ref("alert-success");
    const selectedImage = ref(null);
    const imagePreview = ref("");
    const imageInput = ref(null);
    const form = reactive({
      product_name: "",
      description: "",
      price: "",
      stock: "",
      image: ""
    });

    function clearImagePreview() {
      if (imagePreview.value) URL.revokeObjectURL(imagePreview.value);
      imagePreview.value = "";
    }

    function imageUrl(filename) {
      return `${apiBase}/uploads/${encodeURIComponent(filename)}`;
    }

    function resetForm() {
      editingId.value = null;
      form.product_name = "";
      form.description = "";
      form.price = "";
      form.stock = "";
      form.image = "";
      selectedImage.value = null;
      clearImagePreview();
      error.value = "";
      imageError.value = "";
      if (imageInput.value) imageInput.value.value = "";
    }

    async function readResponse(response) {
      const result = await response.json();
      if (!response.ok || result.error || result.success === false) {
        throw new Error(result.error || result.message || "ดำเนินการไม่สำเร็จ");
      }
      return result;
    }

    async function loadProducts() {
      loading.value = true;
      try {
        const result = await readResponse(await fetch(apiUrl));
        if (!result.success || !Array.isArray(result.data)) {
          throw new Error(result.message || "ไม่สามารถโหลดข้อมูลสินค้าได้");
        }
        products.value = result.data;
      } catch (loadError) {
        error.value = loadError instanceof Error ? loadError.message : "ไม่สามารถโหลดข้อมูลสินค้าได้";
      } finally {
        loading.value = false;
      }
    }

    function selectImage(event) {
      const file = event.target.files && event.target.files[0];
      selectedImage.value = file || null;
      clearImagePreview();
      imageError.value = "";
      if (!file) return;

      const allowedTypes = ["image/jpeg", "image/png", "image/gif", "image/webp"];
      if (!allowedTypes.includes(file.type) || file.size > 5 * 1024 * 1024) {
        imageError.value = "กรุณาเลือกไฟล์ภาพ JPEG, PNG, GIF หรือ WebP ที่มีขนาดไม่เกิน 5 MB";
        selectedImage.value = null;
        event.target.value = "";
        return;
      }

      error.value = "";
      imagePreview.value = URL.createObjectURL(file);
    }

    function editProduct(product) {
      editingId.value = product.product_id;
      form.product_name = product.product_name || "";
      form.description = product.description || "";
      form.price = product.price ?? "";
      form.stock = product.stock ?? 0;
      form.image = product.image || "";
      selectedImage.value = null;
      clearImagePreview();
      error.value = "";
      imageError.value = "";
      message.value = "";
      if (imageInput.value) imageInput.value.value = "";
      window.scrollTo({ top: 0, behavior: "smooth" });
    }

    async function saveProduct() {
      if (imageError.value) return;
      saving.value = true;
      error.value = "";
      message.value = "";
      const data = new FormData();
      data.append("action", editingId.value ? "update" : "add");
      if (editingId.value) data.append("product_id", editingId.value);
      data.append("product_name", form.product_name);
      data.append("description", form.description);
      data.append("price", form.price);
      data.append("stock", form.stock);
      if (selectedImage.value) data.append("image", selectedImage.value);

      try {
        const result = await readResponse(await fetch(apiUrl, { method: "POST", body: data }));
        message.value = result.message || "บันทึกข้อมูลสินค้าสำเร็จ";
        resetForm();
        await loadProducts();
      } catch (saveError) {
        error.value = saveError instanceof Error ? saveError.message : "ไม่สามารถบันทึกข้อมูลสินค้าได้";
      } finally {
        saving.value = false;
      }
    }

    async function deleteProduct(product) {
      if (!window.confirm(`ยืนยันการลบ ${product.product_name} หรือไม่?`)) return;
      saving.value = true;
      error.value = "";
      message.value = "";
      const data = new FormData();
      data.append("action", "delete");
      data.append("product_id", product.product_id);
      try {
        const result = await readResponse(await fetch(apiUrl, { method: "POST", body: data }));
        message.value = result.message || "ลบสินค้าสำเร็จ";
        await loadProducts();
      } catch (deleteError) {
        error.value = deleteError instanceof Error ? deleteError.message : "ไม่สามารถลบสินค้าได้";
      } finally {
        saving.value = false;
      }
    }

    function hideBrokenImage(event) {
      event.target.hidden = true;
    }

    onMounted(loadProducts);
    onBeforeUnmount(clearImagePreview);

    return {
      apiBase, products, loading, saving, editingId, error, imageError, message, messageType,
      selectedImage, imagePreview, imageInput, form, imageUrl, resetForm,
      selectImage, editProduct, saveProduct, deleteProduct, hideBrokenImage
    };
  }
};
</script>

<style scoped>
.product-thumbnail {
  width: 64px;
  height: 64px;
  object-fit: cover;
}

.product-preview {
  display: block;
  max-width: 240px;
  max-height: 180px;
  object-fit: contain;
}
</style>
