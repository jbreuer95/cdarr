<template>
    <MasterLayout title="Radarr">
        <PageToolbar>
            <PageToolBarItem
                :active="form.isDirty"
                icon="floppy-disk"
                title="Save Changes"
                in-active-title="No Changes"
                @click="form.put(route('settings.radarr.update'))"
            ></PageToolBarItem>
            <PageToolBarItem
                v-if="testActive"
                icon="vial"
                title="Test connection"
                @click="form.post(route('settings.radarr.test'))"
            ></PageToolBarItem>
        </PageToolbar>

        <div class="flex flex-1 flex-col p-4">
            <PageHeader title="Radarr connection" />
            <div class="flex flex-1 flex-col max-w-2xl">
                <FormInputText
                    v-model="form.token"
                    :error="form.errors.token"
                    label="API Key"
                    helper="You can find it under Setings -> General -> Security"
                />
                <FormInputText
                    v-model="form.url"
                    :error="form.errors.url"
                    label="URL Base"
                >
                    <template #helper>
                        Internal or external url to Radarr, e.g:<br />
                        http://localhost:7878, http://radarr:7878<br />
                        https://rdarr.example.com, https://example.com/radarr
                    </template>
                </FormInputText>
            </div>
        </div>
    </MasterLayout>
</template>

<script setup>
import PageToolbar from "@/Components/PageToolbar.vue";
import PageToolBarItem from "@/Components/PageToolBarItem.vue";
import FormInputText from "@/Components/FormInputText.vue";
import MasterLayout from "@/Layouts/MasterLayout.vue";
import PageHeader from "@/Components/PageHeader.vue";
import { computed } from "vue";
import { useForm } from "@inertiajs/vue3";
import { usePage } from "@inertiajs/vue3";

const page = usePage();

const form = useForm({
    token: page.props.settings.token,
    url: page.props.settings.url,
});

const testActive = computed(() => {
    return form.token !== "" && form.url !== "";
});
</script>
