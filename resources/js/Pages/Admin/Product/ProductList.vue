<script setup>
import { router, usePage } from "@inertiajs/vue3";
import { ref } from "vue";
import Swal from "sweetalert2";
import { Plus } from '@element-plus/icons-vue'

defineProps({
    products : Array
})

const {categories, brands } = usePage().props;

const dialogVisible = ref(false);
const editMode = ref(false);
const isAddProduct = ref(false);
const id = ref('');
const title = ref('');
const price = ref('');
const quantity = ref('');
const description = ref('');
const productImages = ref([]);
const category_id = ref('');
const brand_id = ref('');
const dialogImageUrl = ref('');
const product_images = ref([]);

const resetFormData = () => {
    id.value = '';
    title.value = '';
    price.value = '';
    quantity.value = '';
    description.value = '';
    category_id.value = '';
    brand_id.value = '';
    productImages.value = [];
    dialogImageUrl.value = '';
};
const handleFileChange = (file) => {
    productImages.value.push(file);
};

const handleRemove = (file) => {
    const index = productImages.value.indexOf(file);
    if (index !== -1) {
        productImages.value.splice(index, 1);
    }
};

const handlePictureCardPreview = (file) => {
    dialogImageUrl.value = file.url;
    dialogVisible.value = true;
};

const openModal = () => {
    isAddProduct.value = true;
    dialogVisible.value = true;
    editMode.value = false;
};

const openEditModel = (product) => {
    console.log(product);
    editMode.value = true;
    isAddProduct.value = false;
    dialogVisible.value = true;

    //update

    id.value=product.id;
    title.value=product.title;
    price.value=product.price;
    quantity.value=product.quantity;
    description.value=product.description;
    category_id.value=product.category_id;
    brand_id.value=product.brand_id;
    product_images.value=product.product_images;


};

const AddProduct = async () => {
    const formData = new FormData();
    formData.append('title', title.value);
    formData.append('price', price.value);
    formData.append('quantity', quantity.value);
    formData.append('description', description.value);
    formData.append('brand_id', brand_id.value);
    formData.append('category_id', category_id.value);
    for (const image of productImages.value) {
        formData.append('product_images[]', image.raw);
    }

    try {
        await router.post('/admin/products/store', formData, {
            onSuccess: (page) => {
                Swal.fire({
                    toast: true,
                    icon: "success",
                    position: 'top-end',
                    showConfirmButton: false,
                    title: page.props.flash.success
                });
            },
        });
    } catch (err) {
        console.log(err);
    } finally {
        dialogVisible.value = false;
        resetFormData();
    }
};

const deleteImage= async (pimage,index) => {

    try {
        await router.delete('/admin/products/image/'+pimage.id,{
            onSuccess : page => {
                product_images.value.splice(index,1);
                Swal.fire({
                    toast :true,
                    icon : "success",
                    position : "top-end",
                    showConfirmButton:false,
                    title : page.props.success
                })
            }
        })
    }catch (err)
    {
        console.log(err)
    }

}

const updateProduct = async () => {
    const formData = new FormData();
    formData.append('title', title.value);
    formData.append('price', price.value);
    formData.append('quantity', quantity.value);
    formData.append('description', description.value);
    formData.append('brand_id', brand_id.value);
    formData.append('category_id', category_id.value);
    formData.append('_method','POST');

    for (const image of productImages.value) {
        formData.append('product_images[]', image.raw);
    }


    try {
        await router.post('/admin/products/update/'+id.value,formData,{
            onSuccess: (page) => {
                dialogVisible.value=false;
                resetFormData();

                Swal.fire({
                    toast: true,
                    icon: "success",
                    position: 'top-end',
                    showConfirmButton: false,
                    title: page.props.flash.success
                });
            },
        })

    }catch (err)
    {
        console.log(err)
    }

}

const deleteProduct = (product,index) => {

     Swal.fire({
         title: 'Are you Sure',
         text: 'This actions cannot undo!',
         icon: "warning",
         showConfirmButton: true,
         showCancelButton :true,
         confirmButtonColor: '#3085d6',
         cancelButtonColor: '#d33',
         confirmButtonText: 'Yes , delete!',
         cancelButtonText: "No, cancel it!",
    }).then((result) => {
         if (result.isConfirmed){
            try {
                router.delete('/admin/products/destroy/'+product.id,{
                    onSuccess: (page) => {
                        this.delete(product,index);


                        Swal.fire({
                            toast: true,
                            icon: "success",
                            position: 'top-end',
                            showConfirmButton: false,
                            title: page.props.flash.success
                        });
                    },
                })
            }catch (err){
                console.log(err)
            }
         }
    })

}




</script>

<template>



    <section class="bg-gray-50 dark:bg-gray-900 p-3 sm:p-5">
        <el-dialog
            v-model="dialogVisible"
            :title="editMode ? 'Edit Product' : 'Add Product' "
            width="500"

        >
            <template #footer>
                <form>
                    <el-form  label-width="auto" style="max-width: 600px">
                        <el-form-item  label="Title">
                            <el-input v-model="title" />
                        </el-form-item>
                        <el-form-item  label="Price">
                            <el-input v-model="price"/>
                        </el-form-item>
                        <el-form-item  label="Quantity">
                            <el-input v-model="quantity" />
                        </el-form-item>
                        <el-form-item label="Category">
                                <select v-model="category_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                    <option v-for="category in categories" :key="category.id" :value="category.id" selected>{{ category.name }}</option>
                                </select>

                        </el-form-item>
                        <el-form-item label="Brand">
                            <select v-model="brand_id"  class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                <option v-for="brand in brands" :key="brand.id" :value="brand.id" selected>{{ brand.name }}</option>

                            </select>
                        </el-form-item>


                        <el-form-item label="Description">
                            <el-input
                                label="Description"
                                v-model="description"
                                style="width: 400px"
                                :rows="2"
                                type="textarea"
                                placeholder="Please input"
                            />
                        </el-form-item>

                        <el-form-item label="Image">
                            <el-upload
                                v-model:file-list="productImages"
                                list-type="picture-card"
                                multiple
                                :on-preview="handlePictureCardPreview"
                                :on-remove="handleRemove"
                                :on-change="handleFileChange"
                            >
                                <el-icon><Plus/></el-icon>
                            </el-upload>
                        </el-form-item>

                        <!--list of images for selected product -->

                        <el-form-item label="Loaded Images">
                            <div class="flex flex-nowrap mb-8">
                                <div class="relative w-32 h-32" v-for="(pimage,index) in product_images" :key="pimage.id">
                                    <img class="w-27 h-27 rounded" :src="`/${pimage.image}`" alt="">
                                    <span class="absolute top-0 right-0 transform -translate-y-1/2 w-3.5 h-3.5 bg-red-400 border-2 border-white dark:border-gray-800 rounded-full">
                                   <span class="text-black text-xs font-bold top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2" @click="deleteImage(pimage,index)">
                                       X
                                   </span>
                               </span>
                                </div>
                            </div>
                        </el-form-item>


                        <!--end -->



                        <el-form-item>
                            <el-button type="primary" @click="editMode ? updateProduct() : AddProduct()" >Submit</el-button>
                            <el-button @click="dialogVisible=false">Cancel</el-button>
                        </el-form-item>
                    </el-form>
                </form>
            </template>
        </el-dialog>
        <div class="mx-auto max-w-screen-xl px-4 lg:px-12">
            <!-- Start coding here -->
            <div class="bg-white dark:bg-gray-800 relative shadow-md sm:rounded-lg overflow-hidden">
                <div class="flex flex-col md:flex-row items-center justify-between space-y-3 md:space-y-0 md:space-x-4 p-4">
                    <div class="w-full md:w-1/2">
                        <form class="flex items-center">
                            <label for="simple-search" class="sr-only">Search</label>
                            <div class="relative w-full">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="currentColor" viewbox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <input type="text" id="simple-search" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full pl-10 p-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="Search" required="">
                            </div>
                        </form>
                    </div>
                    <div class="w-full md:w-auto flex flex-col md:flex-row space-y-2 md:space-y-0 items-stretch md:items-center justify-end md:space-x-3 flex-shrink-0">



                        <button @click="openModal" type="button" class="flex items-center justify-center text-white bg-blue-700 hover:bg-primary-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800">
                            <svg class="h-3.5 w-3.5 mr-2" fill="currentColor" viewbox="0 0 20 20" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path clip-rule="evenodd" fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" />
                            </svg>
                            Add product
                        </button>



<!--                        <div class="flex items-center space-x-3 w-full md:w-auto">-->
<!--                            <button id="actionsDropdownButton" data-dropdown-toggle="actionsDropdown" class="w-full md:w-auto flex items-center justify-center py-2 px-4 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-primary-700 focus:z-10 focus:ring-4 focus:ring-gray-200 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700" type="button">-->
<!--                                <svg class="-ml-1 mr-1.5 w-5 h-5" fill="currentColor" viewbox="0 0 20 20" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">-->
<!--                                    <path clip-rule="evenodd" fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />-->
<!--                                </svg>-->
<!--                                Actions-->
<!--                            </button>-->
<!--                            <div id="actionsDropdown" class="hidden z-10 w-44 bg-white rounded divide-y divide-gray-100 shadow dark:bg-gray-700 dark:divide-gray-600">-->
<!--                                <ul class="py-1 text-sm text-gray-700 dark:text-gray-200" aria-labelledby="actionsDropdownButton">-->
<!--                                    <li>-->
<!--                                        <a href="#" class="block py-2 px-4 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">Mass Edit</a>-->
<!--                                    </li>-->
<!--                                </ul>-->
<!--                                <div class="py-1">-->
<!--                                    <a href="#" class="block py-2 px-4 text-sm text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-gray-200 dark:hover:text-white">Delete all</a>-->
<!--                                </div>-->
<!--                            </div>-->
<!--                            <button id="filterDropdownButton" data-dropdown-toggle="filterDropdown" class="w-full md:w-auto flex items-center justify-center py-2 px-4 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-primary-700 focus:z-10 focus:ring-4 focus:ring-gray-200 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700" type="button">-->
<!--                                <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" class="h-4 w-4 mr-2 text-gray-400" viewbox="0 0 20 20" fill="currentColor">-->
<!--                                    <path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z" clip-rule="evenodd" />-->
<!--                                </svg>-->
<!--                                Filter-->
<!--                                <svg class="-mr-1 ml-1.5 w-5 h-5" fill="currentColor" viewbox="0 0 20 20" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">-->
<!--                                    <path clip-rule="evenodd" fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />-->
<!--                                </svg>-->
<!--                            </button>-->
<!--                        </div>-->


                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-4 py-3">Product name</th>
                            <th scope="col" class="px-4 py-3">Description</th>
                            <th scope="col" class="px-4 py-3">Category</th>
                            <th scope="col" class="px-4 py-3">Brand</th>
                            <th scope="col" class="px-4 py-3">Quantity</th>
                            <th scope="col" class="px-4 py-3">Price</th>
                            <th scope="col" class="px-4 py-3">Stock</th>
                            <th scope="col" class="px-4 py-3">Publish</th>
                            <th scope="col" class="px-4 py-3">
                                <span class="sr-only">Actions</span>
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr class="border-b dark:border-gray-700" v-for="(product,index) in products" :key="product.id">
                            <th scope="row" class="px-4 py-3 font-medium text-gray-900 whitespace-nowrap dark:text-white">{{product.title}}</th>
                            <td class="px-4 py-3">{{ product.description }}</td>
                            <td class="px-4 py-3">{{ product.category.name }}</td>
                            <td class="px-4 py-3">{{ product.brand.name }}</td>
                            <td class="px-4 py-3">{{ product.quantity }}</td>
                            <td class="px-4 py-3">{{ product.price }}</td>
                            <td class="px-4 py-3">
                                <span v-if="product.inStock === 0" class="bg-red-100 text-red-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded dark:bg-red-900 dark:text-red-300">Out of Stock</span>
                                <span v-else class="bg-green-100 text-green-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded dark:bg-green-900 dark:text-green-300">inStock</span>


                            </td>


                            <td class="px-4 py-3">
                                <button v-if="product.published === 0" type="button" class="px-3 py-2 text-xs font-medium text-center text-white bg-green-700 rounded-lg hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-green-300 dark:bg-green-600 dark:hover:bg-green-700 dark:focus:ring-green-800">Published</button>
                                <button v-else type="button" class="px-3 py-2 text-xs font-medium text-center text-white bg-red-700 rounded-lg hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-800">unPusbilshed</button>
                            </td>


                            <td class="px-4 py-3"></td>
                            <td class="px-4 py-3 flex items-center justify-end">
                                <button @click="openEditModel(product)" class=" w-full block py-2 px-4 hover:bg-green-300 dark:hover:bg-green-300 dark:hover:text-white">Edit</button>
                                <button @click="deleteProduct(product,index)" class="w-full block py-2 px-4 hover:bg-red-100 dark:hover:bg-red-600 dark:hover:text-white">Delete</button>
                            </td>
                        </tr>

                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </section>
</template>

<style scoped>

</style>
