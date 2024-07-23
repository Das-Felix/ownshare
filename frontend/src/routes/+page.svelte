<script>
    import { fetchFileCollection, getConfig } from "$lib/api.js";
    import { page } from '$app/stores'
	import { onMount } from "svelte";
    import Handlebars from "handlebars";
    import { base } from '$app/paths'

    $: collection = {};

    $: html = "";
    
    Handlebars.registerHelper('ifEqual', function(arg1, arg2, options) {
        console.log('Comparing:', arg1, 'with', arg2);  // Debugging line
        return (arg1 == arg2) ? options.fn(this) : options.inverse(this);
    });

    onMount(async () => {
        const theme = await getCurrentTheme();

        //Theme CSS
        let link = document.createElement("link");
        link.rel = "stylesheet";
        link.type = "text/css";
        link.href = `api/themes/${theme}/theme.css`;
        document.getElementsByTagName("head")[0].appendChild(link);

        //Theme Config
        let response = await fetch(`api/themes/${theme}/theme.json`);
        let themeConfig = await response.json();
        let themeVars = {};

        themeConfig.variables.forEach(variable => {
            themeVars[variable.name] = variable.value;
        });

        themeConfig.variables = themeVars;

        //Theme HTML
        response = await fetch(`api/themes/${theme}/template.html`);
        let templateHTML = await response.text();
        let template = Handlebars.compile(templateHTML);

        
        let collectionId = $page.url.searchParams.get("q");
        await fetchCollection(collectionId, "");
        html = template({ 
            collection: collection,
            theme: {
                name: theme,
                path: "/api/themes/" + theme,
                config: themeConfig
            }
        });

        document.addEventListener("collectionPasswordEntered", async (event) => {
            let password = document.querySelector('[name="collection_password"]').value
            await fetchCollection(collectionId, password);
            html = template({ collection });
            
        });
    });

    async function fetchCollection(collectionId, collectionPassword) {
        let cfg = await getConfig();
        collection = await fetchFileCollection(collectionId, collectionPassword);

        if(!collection.error) {
            for(let i = 0; i < collection.files.length; i++) {
                collection.files[i].index = i + 1;
                collection.files[i].formatedSize = formatBytes(collection.files[i].size); 
                collection.files[i].url = cfg.backendAddress + collection.files[i].location;
            }

            collection.zipUrl = cfg.backendAddress + collection.path + "/" + collection.collection_id + ".zip";
        } else if(collection.error == "prompt_password") {
            collection.password_prompt = "show";
        } else if(collection.error == "wrong_password") {
            collection.password_prompt = "show";
            collection.wrong_password = "wrong";
        }
    }

    async function getCurrentTheme() {
        let cfg = await getConfig();

        let response = await fetch(cfg.backendAddress + "/public/getCurrentTheme.php");
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
