<script setup lang="ts">
import { onMounted } from "vue";
import MainNav from "@/Components/MainNav.vue";
import Search from "@/Components/Search.vue";
import UserNav from "@/Components/UserNav.vue";
import MeetingList from "@/Components/MeetingList.vue";
import MeetingForm from "@/Components/MeetingForm.vue";
// import { Button } from "@/lib/registry/new-york/ui/button";
import { useToast } from "@/Components/ui/toast/use-toast";
import { Toaster } from "@/Components/ui/toast";
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from "@/lib/registry/new-york/ui/card";
import {
    Tabs,
    TabsContent,
    TabsList,
    TabsTrigger,
} from "@/lib/registry/new-york/ui/tabs";
const { toast } = useToast();

const props = defineProps({
    user_meetings: {
        type: Object,
        default: () => ({}),
    },
    flash: {
        type: Object,
        default: () => ({ success: false, error: false }),
    },
});

onMounted(() => {
    if (props.flash.success) {
        toast({
            title: "Berhasil",
            description: props.flash.success,
        });
    } else if (props.flash.error) {
        toast({
            title: "Uh oh! Gagal.",
            description: props.flash.error,
            variant: "destructive",
        });
    }
});
</script>

<template>
    <div class="grid">
        <!-- <VPImage
            alt="Dashboard"
            width="1280"
            height="1214"
            class="block"
            :image="{
                dark: '/examples/dashboard-dark.png',
                light: '/examples/dashboard-light.png',
            }"
        /> -->
    </div>
    <Toaster />

    <div class="flex-col md:flex">
        <div class="border-b">
            <div class="flex h-16 items-center px-4">
                <!-- <TeamSwitcher /> -->
                <MainNav class="mx-6" />
                <div class="ml-auto flex items-center space-x-4">
                    <Search />
                    <UserNav />
                </div>
            </div>
        </div>
        <div class="flex-1 space-y-4 p-8 pt-6">
            <div class="flex items-center justify-between space-y-2">
                <h2 class="text-3xl font-bold tracking-tight">Dashboard</h2>
                <div class="flex items-center space-x-2">
                    <!-- <DateRangePicker /> -->
                    <!-- <Button>Download</Button> -->
                </div>
            </div>
            <Tabs default-value="overview" class="space-y-4">
                <TabsList>
                    <TabsTrigger value="overview">
                        Daftar Pengajuan
                    </TabsTrigger>
                    <TabsTrigger value="pengajuan">
                        Buat Pengajuan
                    </TabsTrigger>
                </TabsList>
                <TabsContent value="overview" class="space-y-4 flex flex-col">
                    <div class="grid gap-4 flex-grow">
                        <Card class="lg:col-span-4 h-[75vh] overflow-auto">
                            <CardHeader>
                                <CardTitle>Daftar Pengajuan Meeting</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <MeetingList :user_meetings="user_meetings" />
                            </CardContent>
                        </Card>
                    </div>
                </TabsContent>
                <TabsContent value="pengajuan" class="space-y-4 flex flex-col">
                    <div class="grid gap-4 flex-grow">
                        <Card class="lg:col-span-4 h-[75vh] overflow-auto">
                            <CardHeader>
                                <CardTitle>Buat Pengajuan Meeting</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <MeetingForm />
                            </CardContent>
                        </Card>
                    </div>
                </TabsContent>
            </Tabs>
        </div>
    </div>
</template>
