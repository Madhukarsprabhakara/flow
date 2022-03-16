<template>
    <app-layout title="Dashboard">
       

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    
                </div>
            </div>
        </div>
    </app-layout>
</template>

<script>
    import { defineComponent } from 'vue'
    import AppLayout from '@/Layouts/AppLayout.vue'
    import Welcome from '@/Jetstream/Welcome.vue'
    import Welcome_v1 from '@/Pages/Welcome_v1.vue'
    export default defineComponent({
        components: {
            AppLayout,
            Welcome,
            Welcome_v1,
        },
    })
</script>
