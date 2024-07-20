<script>
	import { onMount } from "svelte";
    import { getCurrentTheme, getAllThemes, setTheme, setThemeConfig, getConfig } from "$lib/api.js";
    import { base } from '$app/paths'
    import { addMessage } from "$lib/stores.js"; 
	import UploadThemeModal from "../../../lib/components/UploadThemeModal.svelte";

    let pageError = "";
    let loading = true;
    let cfg = null;

    let availableThemes = [];
    let currentTheme = {}

    let showUploadThemeModal = false;

    onMount(async () => {
        await fetchData();
    });

    async function fetchData() {
        cfg = await getConfig();
        if(cfg.error != null) {
            pageError += cfg.error;
            return;
        }

        //Get Theme Name
        let theme = await getCurrentTheme();
        if(theme.error != null) {
            pageError += theme.error;
        }
        currentTheme.name = theme.currentTheme;

        //Get theme config file
        let response = await fetch(`${base}/api/themes/${currentTheme.name}/theme.json`);
        let themeConfig = await response.json();
        currentTheme.config = themeConfig;

        //Get all available themes;
        let themeNames = await getAllThemes();
        if(themeNames.error) {
            addMessage(themeNames.error, "error");
        } else {

            availableThemes = [];
            themeNames.forEach(async themeName => {
                let response = await fetch(`${base}/api/themes/${themeName}/theme.json`);
                let themeConfig = await response.json();
                availableThemes = [...availableThemes, {
                    name: themeName,
                    config: themeConfig
                }];
            }); 

        }


        loading = false;
    }

    async function saveThemeVariableChanges() {
        let config = structuredClone(currentTheme.config);

        for(let i = 0; i < config.variables.length; i++) {
            let variable = config.variables[i];
            const newVal = document.querySelector(`[name="${variable.name}"]`).value;
            config.variables[i].value = newVal;
        }

        let response = await setThemeConfig(currentTheme.name, config);

        if(response.error) {
            addMessage(response.error, "error");
        } else {
            addMessage(response.message, "success");
        }

    }

</script>


<UploadThemeModal bind:showModal={showUploadThemeModal} on:uploaded={async () => { fetchData(); }}></UploadThemeModal>

<div class="flex justify-between items-center mb-4"> 
    <h1 class="text-4xl font-bold">Themes</h1>

    <div class="flex gap-2">
        <button class="btn btn-sm" on:click={() => {showUploadThemeModal = true}}>Upload Theme</button>
    </div>
</div>

<div>
    {#if pageError != ""}
        <p>Error: {pageError}</p>
    {/if}
    {#if pageError == ""}
        {#if loading}
            <div class="w-full flex justify-center items-center">
                <span class="loading loading-dots loading-lg"></span>
            </div>
        {:else}

            <ul class="themes-grid w-full">
                {#each availableThemes as theme}
                    <div class="card bg-base-100 shadow-xl">
                        <figure>
                        <img
                            src="{base}/api/themes/{theme.name}/assets/thumbnail.png"
                            alt="Shoes" />
                        </figure>
                        <div class="card-body p-6">
                            <h2 class="card-title justify-between items-center text-base h-full">
                                {theme.config.title}
                                {#if currentTheme.name == theme.name}
                                    <div class="badge badge-secondary">ACTIVE</div>
                                {:else}
                                    <button class="btn btn-sm" on:click={async () => {
                                        let response = await setTheme(theme.name);

                                        if(response.error) {
                                            addMessage(response.error, "error");
                                        } else {
                                            addMessage(response.message, "success");
                                            await fetchData();
                                        }
                                    }}>Activate</button>
                                {/if}
                            </h2>
                        </div>
                    </div>
                {/each} 
            </ul>

            <section class="mt-10">
                <h2 class="text-2xl font-bold">Theme Variables</h2>

                {#each currentTheme.config.variables as variable}
                    {#if variable.type == "select"}
                        <label class="form-control w-full" for="{variable.name}">
                            <div class="label"><span class="label-text">{variable.title}</span></div>
                            <select class="select select-bordered w-full max-w-lg" name="{variable.name}" value={variable.value}>
                                {#each variable.options as option}
                                    <option value="{option}">{option}</option>
                                {/each}
                            </select>
                        </label>
                    {:else if variable.type == "color"}
                        <label class="form-control w-full" for="{variable.name}">
                            <div class="label"><span class="label-text">{variable.title}</span></div>
                            <input type="{variable.type}" class="input input-bordered w-full max-w-lg p-0" name="{variable.name}" value="{variable.value}"/>
                        </label>
                    {:else}
                        <label class="form-control w-full" for="{variable.name}">
                            <div class="label"><span class="label-text">{variable.title}</span></div>
                            <input type="{variable.type}" class="input input-bordered w-full max-w-lg" name="{variable.name}" value="{variable.value}"/>
                        </label>
                    {/if}
                    
                {/each}  

                <button class="btn btn-primary mt-6" on:click={saveThemeVariableChanges}>Save changes</button>
            </section>
        {/if}
        
    {/if} 

    
</div>


<style>
    .themes-grid {
        --auto-grid-min-size: 15rem;
  
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(var(--auto-grid-min-size), 1fr));
        grid-gap: 1rem;
    }
</style>