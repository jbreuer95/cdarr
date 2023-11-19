import axios from "axios";
import { ref } from "vue";

export function useInfiniteScrolling(paginator) {
    const items = ref(paginator.data)
    const nextPageUrl = ref(paginator.next_page_url)

    const loadNextPage = async () => {
        if (! nextPageUrl.value) {
            return;
        }

        try {
            const { data: movies } = await axios.get(nextPageUrl.value)
            items.value = [...items.value, ...movies.data]
            nextPageUrl.value = movies.next_page_url;
        } catch (error) {
            return;
        }
    }

    const start = (ref) => {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    loadNextPage();
                }
            })
        }, {
            root: document.querySelector('main'),
            rootMargin: '0px 0px 300px 0px'
        })

        observer.observe(ref.value)
    }

    return { start, items, nextPageUrl }
}
