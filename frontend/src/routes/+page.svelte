<script>
    import { fetchFileCollection, getConfig } from "$lib/api.js";
    import { page } from '$app/stores'
	import { onMount } from "svelte";
    import Handlebars from "handlebars";
    import { base } from '$app/paths'

    $: collection = {};

    $: html = "";
    

    onMount(async () => {
        const cfg = await getConfig();
        const theme = await getCurrentTheme(cfg);

        //Theme CSS
        let link = document.createElement("link");
        link.rel = "stylesheet";
        link.type = "text/css";
        link.href = `api/themes/${theme}/theme.css`;
        document.getElementsByTagName("head")[0].appendChild(link);

        //Theme HTML
        let response = await fetch(`api/themes/${theme}/template.html`);
        let templateHTML = await response.text();
        let template = Handlebars.compile(templateHTML);

        let collectionId = $page.url.searchParams.get("q");
        await fetchCollection(collectionId, "", cfg);
        html = template({ collection });

        document.addEventListener("collectionPasswordEntered", async (event) => {
            let password = document.querySelector('[name="collection_password"]').value
            await fetchCollection(collectionId, password, cfg);
            html = template({ collection });
            
        });
    });

    async function fetchCollection(collectionId, collectionPassword, cfg) {
        let backendAddress = cfg.backendAddress;

        collection = await fetchFileCollection(collectionId, collectionPassword);

        console.log(collection)

        if(!collection.error) {
            for(let i = 0; i < collection.files.length; i++) {
                collection.files[i].index = i + 1;
                collection.files[i].formatedSize = formatBytes(collection.files[i].size); 
                collection.files[i].url = backendAddress + collection.files[i].location;
            }

            collection.zipUrl = backendAddress + collection.path + "/" + collection.collection_id + ".zip";
        } else if(collection.error == "prompt_password") {
            collection.password_prompt = "show";
        } else if(collection.error == "wrong_password") {
            collection.password_prompt = "show";
            collection.wrong_password = "wrong";
        }
    }

    async function getCurrentTheme(cfg) {
        let backendAddress = cfg.backendAddress;

        let response = await fetch(backendAddress + "/public/getCurrentTheme.php");
        let json = await response.json();
        return json.currentTheme;
    }

    function formatBytes(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

</script>

{@html html }

<!-- <div class="w-full h-screen flex items-center justify-center bg-base-300">
    <div class="card bg-base-100 p-8">
        <h1 class="text-center text-2xl font-bold mb-4">Download Files</h1>
        
        <div class="flex flex-col gap-4">
            {#if collection.files != null}
                {#each collection.files as file}
                    <a class="btn btn-primary" download href="{PUBLIC_BACKEND_ADDRESS + file.location}">{file.name}</a>
                {/each}

                <a class="btn btn-primary" download href="{PUBLIC_BACKEND_ADDRESS + collection.path + "/" + collection.collection_id + ".zip"}">Download All</a>
            {/if}

        </div>
    </div>
</div> -->