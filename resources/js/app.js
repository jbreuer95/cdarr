import "../css/fonts.css";
import "../css/app.css";

import { createApp, h } from "vue";
import { createPinia } from "pinia";
import { createInertiaApp } from "@inertiajs/vue3";
import { ZiggyVue } from "ziggy";
import loadIcons from "./icons";

const appName = window.document.getElementsByTagName("title")[0]?.innerText;

loadIcons();
createInertiaApp({
    title: (title) => {
        return title ? `${title} - ${appName}` : appName;
    },
    resolve: (name) => {
        const pages = import.meta.glob("./Pages/**/*.vue", { eager: true });
        return pages[`./Pages/${name}.vue`];
    },
    setup({ el, App, props, plugin }) {
        const pinia = createPinia();
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(pinia)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: {
        color: "#16A34A",
    },
});
