<script>
    import { getConfig } from "$lib/api.js";
    import { addMessage, messages } from "$lib/stores.js";
	import { onMount } from "svelte";
    import { goto } from '$app/navigation';
    import { base } from '$app/paths';

    let config;

    let setupStep = 0;
    let setupActive = true;

    let dbHost = "";
    let dbName = "";
    let dbUser = "";
    let dbPassword = "";
    let dbErr = "";

    let adminUsername = "";
    let adminEmail = "";
    let adminPassword = "";
    let adminPasswordRepeat = "";
    let adminErr = "";

    onMount(async () => {
        config = await getConfig();
        if(config.error) {
            addMessage("Error while loading config!", "error");
            return;
        }

        //Checking if setup is already completed!
        let setupCheck = await fetch(config.backendAddress + "/installer.php");
        setupCheck = await setupCheck.json();

        if(setupCheck.error == "setup-already-complete") {
            addMessage("Setup already complete!", "error");
            goto(base + "/auth/login");
            setupActive = false;
        }

    
    });

    function startSetup() { 
        if(!setupActive) return;
        setupStep = 1;
    }

    async function connectDatabase() {

        let fd = new FormData();
        fd.set("action", "set_db_credentials");
        fd.set("db_host", dbHost);
        fd.set("db_name", dbName);
        fd.set("db_user", dbUser);
        fd.set("db_password", dbPassword);

        let result = await fetch(config.backendAddress + "/installer.php", {
            method: "POST",
            body: fd,
        });

        let response = await result.json();

        if(response.error) {
            dbErr = response.error;
            return;
        }

        /*Calling the setup function to create db tables and set default values*/
        fd = new FormData();
        fd.set("action", "init_app");

        result = await fetch(config.backendAddress + "/installer.php", {
            method: "POST",
            body: fd,
        });

        response = await result.json();

        if(response.error) {
            addMessage(result.error, "error");
            setupActive = false;
            return;
        }

        setupStep++;

    }

    async function createAdminUser() {

        let fd = new FormData();
        fd.set("action", "create_admin_user");
        fd.set("username", adminUsername);
        fd.set("email", adminEmail);
        fd.set("password", adminPassword);
        fd.set("password_repeat", adminPasswordRepeat);

        let result = await fetch(config.backendAddress + "/installer.php", {
            method: "POST",
            body: fd,
        });

        let response = await result.json();

        if(response.error) {
            adminErr = response.error;
            return;
        }

        /* Calling the finish function to close the setup */
        fd = new FormData();
        fd.set("action", "finish_setup");

        result = await fetch(config.backendAddress + "/installer.php", {
            method: "POST",
            body: fd,
        });

        response = await result.json();

        if(response.error) {
            addMessage(response.error, "error");
        } else {
            goto(base + "/auth/login");
        }
    }

</script>

<div class="w-full h-screen flex justify-center items-center">

    <div class="card bg-base-300 p-8 overflow-hidden">
        <div class="w-96 overflow-hidden">
            <div class="w-content flex flex-row" style="transform: translateX({ setupStep * -24 + "rem" });">
                <!-- Landing -->
                <div class="setup-step">
                    <div>
                        <h1 class="text-3xl font-bold">OwnShare Setup</h1>
                        <h2 class="text-1xl font-bold">Thanks for using OwnShare!</h2>
                        <br>
                        <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Dolores ipsam dolorum quod beatae laborum. Quo praesentium dolor quod vitae, fugiat fuga saepe qui iure nemo sint nostrum quasi rerum veniam!</p>
                    </div>
    
                    <div class="mt-8">
                        <button class="btn btn-primary" on:click={startSetup}>Start Setup</button>
                    </div>
                </div>

                <!-- Database Connection -->
                <div class="setup-step">
                    <div>
                        <h1 class="text-3xl font-bold">Database Credentials</h1>

                        <span class="text-red-600">{ dbErr }</span>

                        <label class="form-control w-full">
                            <div class="label"><span class="label-text">Database host</span></div>
                            <input type="text" class="input input-bordered w-full max-w-lg" bind:value={dbHost}/>
                        </label>      
                        <label class="form-control w-full">
                            <div class="label"><span class="label-text">Database name</span></div>
                            <input type="text" class="input input-bordered w-full max-w-lg" bind:value={dbName}/>
                        </label>      
                        <label class="form-control w-full">
                            <div class="label"><span class="label-text">Database user</span></div>
                            <input type="text" class="input input-bordered w-full max-w-lg" bind:value={dbUser}/>
                        </label>      
                        <label class="form-control w-full">
                            <div class="label"><span class="label-text">Database password</span></div>
                            <input type="text" class="input input-bordered w-full max-w-lg" bind:value={dbPassword}/>
                        </label>                     

                    </div>
        
                    <div class="mt-8">
                        <button class="btn btn-primary" on:click={connectDatabase}>Connect database</button>
                    </div>
                </div>

                <!--Create Admin User-->

                <div class="setup-step">
                    <div>
                        <h1 class="text-3xl font-bold">Admin User</h1>

                        <span class="text-red-600">{ adminErr }</span>

                        <label class="form-control w-full">
                            <div class="label"><span class="label-text">Username</span></div>
                            <input type="text" class="input input-bordered w-full max-w-lg" bind:value={adminUsername}/>
                        </label>      
                        <label class="form-control w-full">
                            <div class="label"><span class="label-text">E-Mail</span></div>
                            <input type="text" class="input input-bordered w-full max-w-lg" bind:value={adminEmail}/>
                        </label>      
                        <label class="form-control w-full">
                            <div class="label"><span class="label-text">Password</span></div>
                            <input type="password" class="input input-bordered w-full max-w-lg" bind:value={adminPassword}/>
                        </label>      
                        <label class="form-control w-full">
                            <div class="label"><span class="label-text">Repeat Password</span></div>
                            <input type="password" class="input input-bordered w-full max-w-lg" bind:value={adminPasswordRepeat}/>
                        </label>                     

                    </div>
        
                    <div class="mt-8">
                        <button class="btn btn-primary" on:click={createAdminUser}>Finish Setup</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="toast toast-end">
    <div class="alert-error"></div>
    <div class="alert-info"></div>
    <div class="alert-success"></div>

    {#each $messages as msg, i}
        <div class="alert alert-{msg.type} flex justify-between">
            <span>{msg.message}</span>
            <button class="btn btn-circle btn-ghost btn-xs" on:click={() => {
                messages.update(val => {
                    val.splice(i, 1);
                    return val;
                });
            }}>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
    {/each}
</div>


<style>
    .setup-step {
        width: 100%;
        display: flex;
        flex-shrink: 0;
        flex-direction: column;
        justify-content: space-between;
    }
</style>