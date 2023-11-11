<template>
    <MasterLayout title="Radarr">
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
                :success="testSuccess"
                @click="test"
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
import { computed, ref, nextTick } from "vue";
import { useForm } from "@inertiajs/vue3";
import { usePage } from "@inertiajs/vue3";

const page = usePage();

const form = useForm({
    token: page.props.settings.token,
    url: page.props.settings.url,
});

const testSuccess = ref(false)

const testActive = computed(() => {
    return form.token !== "" && form.url !== "";
});

const update = () => {
    if (! form.isDirty) {
        return;
    }

    form.put(route("settings.radarr.update"), {
        onSuccess: () => {
            form.defaults("url", page.props.settings.url);
            form.reset("url");
        },
    });
};

const test = () => {
    form.clearErrors();
    form.post(route("settings.radarr.test"), {
        onSuccess: () => {
            testSuccess.value = true
            setTimeout(() => {
                testSuccess.value = false
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
