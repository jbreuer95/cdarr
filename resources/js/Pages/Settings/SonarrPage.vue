<template>
    <MasterLayout title="Sonarr">
        <PageToolbar>
            <PageToolBarItem
                :active="form.isDirty"
                icon="floppy-disk"
                title="Save Changes"
                in-active-title="No Changes"
                @click="update"
            ></PageToolBarItem>
            <PageToolBarItem
                v-if="testActive"
                icon="vial"
                title="Test connection"
                :loading="form.processing"
                :success="testSuccess"
                @click="test"
            ></PageToolBarItem>
        </PageToolbar>

        <div class="flex flex-1 flex-col p-4">
            <PageHeader title="Sonarr connection" />
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
                        Internal or external url to Sonarr, e.g:<br />
                        http://localhost:7878, http://sonarr:7878<br />
                        https://rdarr.example.com, https://example.com/sonarr
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
import { computed, ref, nextTick } from "vue";
import { useForm } from "@inertiajs/vue3";

const props = defineProps({
    settings: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    token: props.settings.token,
    url: props.settings.url,
});

const testSuccess = ref(false);

const testActive = computed(() => {
    return form.token !== "" && form.url !== "";
});

const update = () => {
    if (!form.isDirty || form.processing) {
        return;
    }
    testSuccess.value = false;

    form.put(route("settings.sonarr.update"), {
        onSuccess: () => {
            form.defaults("url", props.settings.url);
            form.reset("url");
        },
    });
};

const test = () => {
    if (form.processing) {
        return;
    }

    form.clearErrors();
    form.post(route("settings.sonarr.test"), {
        onSuccess: () => {
            testSuccess.value = true;
            setTimeout(() => {
                testSuccess.value = false;
            }, 3000);

            let token = form.token;
            let url = form.url;
            form.reset();

            nextTick(() => {
                form.token = token;
                form.url = url;
            });
        },
    });
};
</script>
