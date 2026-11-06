<template>
  <div class="container my-5">
    <h2 class="text-center mb-4">รายการสินค้า</h2>
    <div v-if="loading" class="text-center py-4">กำลังโหลดข้อมูลสินค้า...</div>
    <div v-else-if="error" class="alert alert-danger" role="alert">{{ error }}</div>
    <div v-else-if="products.length === 0" class="alert alert-info text-center">
      ยังไม่มีสินค้า
    </div>
    <div v-else class="row">
      <div class="col-md-3" v-for="product in products" :key="product.product_id">
        <div class="card shadow-sm mb-4">
          <img
            v-if="product.image"
            :src="`${apiBase}/uploads/${encodeURIComponent(product.image)}`"
            style="height: 300px; object-fit: cover"
            class="card-img-top"
            :alt="product.product_name"
            @error="hideBrokenImage"
          >
          <div class="card-body text-center">
            <h5 class="card-title">{{ product.product_name }}</h5>
            <p class="card-text">{{ product.price }} บาท</p>
            <button class="btn btn-primary">สั่งซื้อ</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>


<script>
import { ref, onMounted } from "vue";
import { API_BASE } from "@/config";

export default {
  name: "ProductCatalog",
  setup() {
    const products = ref([]);
    const loading = ref(true);
    const error = ref(null);
    const apiBase = API_BASE.replace(/\/$/, "");

    const fetchProducts = async () => {
      try {
        const response = await fetch(`${apiBase}/show_product.php`);

        if (!response.ok) {
          throw new Error("ไม่สามารถดึงข้อมูลได้");
        }

        const result = await response.json();
        if (result.success && Array.isArray(result.data)) {
          products.value = result.data;
        } else {
          throw new Error(result.message || "ไม่สามารถดึงข้อมูลสินค้าได้");
        }

      } catch (err) {
        error.value = err instanceof Error ? err.message : "ไม่สามารถดึงข้อมูลสินค้าได้";
      } finally {
        loading.value = false;
      }
    };

    const hideBrokenImage = (event) => {
      event.target.hidden = true;
    };

    onMounted(fetchProducts);

    return {
      products,
      loading,
      error,
      apiBase,
      hideBrokenImage
    };
  }
};
</script>
