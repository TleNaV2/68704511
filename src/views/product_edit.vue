<template>
  <main class="container my-4">
    <h1 class="h2 mb-4">จัดการข้อมูลอุปกรณ์</h1>

    <div v-if="message" class="alert" :class="messageType" role="status">{{ message }}</div>

    <button type="button" class="btn btn-primary mb-3" @click="openAddModal">เพิ่มข้อมูลอุปกรณ์</button>

    <div v-if="loading" class="text-center py-4">กำลังโหลดข้อมูล...</div>
    <div v-else-if="assets.length === 0" class="alert alert-info">ไม่พบข้อมูลอุปกรณ์</div>
    <div v-else class="table-responsive asset-table-wrap">
      <table class="table table-bordered table-striped align-middle asset-table">
        <thead class="table-light">
          <tr>
            <th>asset_id</th>
            <th>asset_code</th>
            <th>asset_name</th>
            <th>purchase_date</th>
            <th>price</th>
            <th>image</th>
            <th>จัดการ</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="asset in assets" :key="asset.asset_id">
            <td>{{ asset.asset_id }}</td>
            <td>{{ asset.asset_code }}</td>
            <td>{{ asset.asset_name }}</td>
            <td>{{ asset.purchase_date || "-" }}</td>
            <td>{{ formatPrice(asset.price) }}</td>
            <td>
              <img
                v-if="asset.image"
                :src="imageUrl(asset.image)"
                :alt="asset.asset_name"
                class="asset-thumbnail"
                @error="hideBrokenImage"
              >
              <span>{{ asset.image || "-" }}</span>
            </td>
            <td class="text-nowrap">
              <button type="button" class="btn btn-warning btn-sm me-2" @click="editAsset(asset)">แก้ไข</button>
              <button type="button" class="btn btn-danger btn-sm" @click="deleteAsset(asset)">ลบ</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div id="assetModal" class="modal fade" tabindex="-1" aria-labelledby="assetModalTitle" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
          <form @submit.prevent="saveAsset">
            <div class="modal-header">
              <h2 id="assetModalTitle" class="modal-title fs-5">
                {{ editing ? "แก้ไขข้อมูลอุปกรณ์" : "เพิ่มข้อมูลอุปกรณ์" }}
              </h2>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
            </div>
            <div class="modal-body">
              <div v-if="formError" class="alert alert-danger" role="alert">{{ formError }}</div>
              <div class="d-flex flex-column gap-3">
                <div>
                  <label for="asset-code" class="form-label">รหัสครุภัณฑ์</label>
                  <input id="asset-code" v-model.trim="form.asset_code" class="form-control" maxlength="50" required>
                </div>
                <div>
                  <label for="asset-name" class="form-label">ชื่อครุภัณฑ์</label>
                  <input id="asset-name" v-model.trim="form.asset_name" class="form-control" maxlength="255" required>
                </div>
                <div>
                  <label for="purchase-date" class="form-label">วันที่ซื้อ</label>
                  <input id="purchase-date" v-model="form.purchase_date" type="date" class="form-control">
                </div>
                <div>
                  <label for="asset-price" class="form-label">ราคา</label>
                  <input
                    id="asset-price"
                    v-model="form.price"
                    type="number"
                    min="0"
                    max="99999999.99"
                    step="0.01"
                    class="form-control"
                  >
                </div>
                <div>
                  <label for="asset-image" class="form-label">รูปภาพ</label>
                  <input
                    id="asset-image"
                    ref="imageInput"
                    type="file"
                    accept="image/jpeg,image/png,image/gif,image/webp"
                    class="form-control"
                    @change="selectImage"
                  >
                  <div class="form-text">เลือกไฟล์รูปภาพ (JPEG, PNG, GIF หรือ WebP) ขนาดไม่เกิน 5 MB</div>
                  <img
                    v-if="imagePreview || (editing && form.image)"
                    :src="imagePreview || imageUrl(form.image)"
                    alt="ตัวอย่างรูปภาพอุปกรณ์"
                    class="asset-preview mt-2"
                    @error="hideBrokenImage"
                  >
                  <div v-if="selectedImage" class="small text-muted mt-1">{{ selectedImage.name }}</div>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
              <button type="submit" class="btn btn-primary" :disabled="saving">
                {{ saving ? "กำลังบันทึก..." : editing ? "บันทึกการแก้ไข" : "เพิ่มข้อมูล" }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </main>
</template>

<script>
import { onMounted, ref } from "vue";
import { API_BASE } from "@/config";

export default {
  name: "AssetManagement",
  setup() {
    const apiUrl = `${API_BASE.replace(/\/$/, "")}/api_assets.php`;
    const assets = ref([]);
    const loading = ref(true);
    const saving = ref(false);
    const editing = ref(false);
    const message = ref("");
    const messageType = ref("alert-danger");
    const formError = ref("");
    const form = ref(emptyForm());
    const imageInput = ref(null);
    const selectedImage = ref(null);
    const imagePreview = ref("");
    let previewObjectUrl = null;
    let modalInstance = null;

    function emptyForm() {
      return {
        asset_id: "",
        asset_code: "",
        asset_name: "",
        purchase_date: "",
        price: "",
        image: ""
      };
    }

    async function readResponse(response) {
      const result = await response.json();
      if (!response.ok || result.error) {
        throw new Error(result.error || "ไม่สามารถดำเนินการได้");
      }
      return result;
    }

    async function fetchAssets() {
      loading.value = true;
      try {
        const result = await readResponse(await fetch(apiUrl));
        if (!Array.isArray(result.data)) throw new Error("ข้อมูลจากเซิร์ฟเวอร์ไม่ถูกต้อง");
        assets.value = result.data;
      } catch (error) {
        showMessage(error.message || "โหลดข้อมูลไม่สำเร็จ");
      } finally {
        loading.value = false;
      }
    }

    function showMessage(text, type = "alert-danger") {
      message.value = text;
      messageType.value = type;
    }

    function clearSelectedImage() {
      if (previewObjectUrl) URL.revokeObjectURL(previewObjectUrl);
      previewObjectUrl = null;
      selectedImage.value = null;
      imagePreview.value = "";
      if (imageInput.value) imageInput.value.value = "";
    }

    function selectImage(event) {
      const file = event.target.files && event.target.files[0];
      clearSelectedImage();
      if (!file) return;

      selectedImage.value = file;
      previewObjectUrl = URL.createObjectURL(file);
      imagePreview.value = previewObjectUrl;
    }

    function imageUrl(image) {
      return `${API_BASE.replace(/\/$/, "")}/uploads/${encodeURIComponent(image)}`;
    }

    function hideBrokenImage(event) {
      event.target.hidden = true;
    }

    function openModal() {
      const modalElement = document.getElementById("assetModal");
      if (!window.bootstrap || !window.bootstrap.Modal) {
        showMessage("ไม่สามารถเปิดหน้าต่างแก้ไขได้ เนื่องจาก Bootstrap ยังไม่พร้อมใช้งาน");
        return;
      }
      modalInstance = window.bootstrap.Modal.getOrCreateInstance(modalElement);
      modalInstance.show();
    }

    function openAddModal() {
      clearSelectedImage();
      editing.value = false;
      form.value = emptyForm();
      formError.value = "";
      message.value = "";
      openModal();
    }

    function editAsset(asset) {
      clearSelectedImage();
      editing.value = true;
      form.value = {
        asset_id: asset.asset_id,
        asset_code: asset.asset_code,
        asset_name: asset.asset_name,
        purchase_date: asset.purchase_date || "",
        price: asset.price === null ? "" : asset.price,
        image: asset.image || ""
      };
      formError.value = "";
      message.value = "";
      openModal();
    }

    function resetForm() {
      clearSelectedImage();
      editing.value = false;
      form.value = emptyForm();
      formError.value = "";
    }

    async function saveAsset() {
      saving.value = true;
      formError.value = "";
      const data = new FormData();
      data.append("action", editing.value ? "update" : "add");
      if (editing.value) data.append("asset_id", form.value.asset_id);
      data.append("asset_code", form.value.asset_code);
      data.append("asset_name", form.value.asset_name);
      data.append("purchase_date", form.value.purchase_date);
      data.append("price", form.value.price);
      if (selectedImage.value) data.append("image", selectedImage.value);

      try {
        const result = await readResponse(await fetch(apiUrl, { method: "POST", body: data }));
        modalInstance.hide();
        resetForm();
        await fetchAssets();
        showMessage(result.message, "alert-success");
      } catch (error) {
        formError.value = error.message || "บันทึกข้อมูลไม่สำเร็จ";
      } finally {
        saving.value = false;
      }
    }

    async function deleteAsset(asset) {
      if (!window.confirm(`ยืนยันการลบ ${asset.asset_name} หรือไม่?`)) return;

      const data = new FormData();
      data.append("action", "delete");
      data.append("asset_id", asset.asset_id);
      try {
        const result = await readResponse(await fetch(apiUrl, { method: "POST", body: data }));
        if (editing.value && String(form.value.asset_id) === String(asset.asset_id)) resetForm();
        await fetchAssets();
        showMessage(result.message, "alert-success");
      } catch (error) {
        showMessage(error.message || "ลบข้อมูลไม่สำเร็จ");
      }
    }

    function formatPrice(price) {
      if (price === null || price === "") return "-";
      return Number(price).toLocaleString("th-TH", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      });
    }

    onMounted(fetchAssets);

    return {
      assets,
      loading,
      saving,
      editing,
      message,
      messageType,
      formError,
      form,
      imageInput,
      selectedImage,
      imagePreview,
      openAddModal,
      editAsset,
      resetForm,
      saveAsset,
      deleteAsset,
      formatPrice,
      selectImage,
      imageUrl,
      hideBrokenImage
    };
  }
};
</script>

<style scoped>
.asset-table-wrap {
  max-width: 900px;
  margin: 0 auto;
}

.asset-table th,
.asset-table td {
  padding: 1rem 0.75rem;
  line-height: 1.5;
}

.asset-thumbnail {
  display: block;
  width: 72px;
  height: 56px;
  margin-bottom: 0.25rem;
  object-fit: cover;
}

.asset-preview {
  display: block;
  max-width: 100%;
  max-height: 240px;
  object-fit: contain;
}
</style>
