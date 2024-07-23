import { base } from '$app/paths'
import { addMessage } from "$lib/stores.js";

export async function getConfig() {
    let response = await fetch(base + "/config.json");
    let result = await response.json();
    return result;
}


// OPTIONS

export async function getOption(option) {
    const cfg = await getConfig();
    const backendAddress = cfg.backendAddress;

    let response = await fetch(backendAddress + "/admin/options/getOption.php?option=" + option, {
        method: "GET",
        credentials: "include",
    });

    let result = await response.json();

    if(result.error != null) {
        console.log("error while fetching option: " + option);
        addMessage(result.error, "error");
        return "";
    }
    return result.value;
}

export async function setOption(name, value) {
    const cfg = await getConfig();
    const backendAddress = cfg.backendAddress;

    let response = await fetch(backendAddress + "/admin/options/setOption.php", {
        method: "POST",
        credentials: "include",
        body: JSON.stringify({
            optionName: name,
            optionValue: value
        })
    });

    let result = await response.json();
    return result;
}




// FILE COLLECTION MANAGEMENT

export async function fetchFileCollection(id, password) {
    const cfg = await getConfig();
    const backendAddress = cfg.backendAddress;

    if(password == null) {
        password = "";
    }

    let result = await fetch(backendAddress + "/public/getCollection.php?collectionId=" + id + "&password=" + password, {
        method: "GET",
        credentials: "include",
    });

    let collections = await result.json();
    return collections;
}

export async function fetchFileCollections(sortField, sortDir, page, perPage) {
    const cfg = await getConfig();
    const backendAddress = cfg.backendAddress;

    if(sortField == null) sortField = "id";
    if(sortDir == null) sortDir = "ASC";

    let result = await fetch(backendAddress + "/admin/files/getCollections.php?sortField=" + sortField + "&sortDir=" + sortDir + "&page=" + page + "&perPage=" + perPage, {
        method: "GET",
        credentials: "include"
    });

    let collections = await result.json();
    return collections;
}

export async function deleteFileCollections(id) {
    const cfg = await getConfig();
    const backendAddress = cfg.backendAddress;

    let fd = new FormData();
    fd.set("collection_id", id);

    let response = await fetch(backendAddress + "/admin/files/deleteCollection.php", {
        method: "POST",
        credentials: "include",
        body: fd
    });

    let result = await response.json();
    return result;
}




// USER MANAGEMENT

export async function fetchUsers() {
    const cfg = await getConfig();
    const backendAddress = cfg.backendAddress;

    let result = await fetch(backendAddress + "/admin/users/getUsers.php", {
        method: "GET",
        credentials: "include"
    });

    let users = await result.json();
    return users;
}

export async function createUser(username, email, password, role) {
    const cfg = await getConfig();
    const backendAddress = cfg.backendAddress;
    
    let fd = new FormData();
    fd.set("username", username);
    fd.set("email", email);
    fd.set("password", password);
    fd.set("role", role);

    let response = await fetch(backendAddress + "/admin/users/createUser.php", {
        method: "POST",
        credentials: "include",
        body: fd
    });

    let result = await response.text();
    console.log(result);
    return JSON.parse(result);
} 

export async function deleteUser(id) {
    const cfg = await getConfig();
    const backendAddress = cfg.backendAddress;
    
    let fd = new FormData();
    fd.set("user_id", id);

    let response = await fetch(backendAddress + "/admin/users/deleteUser.php", {
        method: "POST",
        credentials: "include",
        body: fd
    });

    let result = await response.text();
    console.log(result);
    return JSON.parse(result);
} 



// Theme

export async function getCurrentTheme() {
    let cfg = await getConfig();

    let response = await fetch(cfg.backendAddress + "/public/getCurrentTheme.php");
    let json = await response.json();
    return json;
}

export async function getAllThemes() {
    let cfg = await getConfig();

    let response = await fetch(cfg.backendAddress + "/admin/theme/getAllThemes.php", {
        method: "GET",
        credentials: "include",
    });
    let json = await response.json();
    return json;
}

export async function setTheme(themeName) {
    let cfg = await getConfig();   

    let fd = new FormData();
    fd.set("theme", themeName);

    let response = await fetch(cfg.backendAddress + "/admin/theme/setTheme.php", {
        method: "POST",
        credentials: "include",
        body: fd
    });
    let json = await response.json();
    return json;
}

export async function setThemeConfig(themeName, themeConfig) {
    let cfg = await getConfig();   

    let fd = new FormData();
    fd.set("theme", themeName);
    fd.set("config", JSON.stringify(themeConfig));

    let response = await fetch(cfg.backendAddress + "/admin/theme/setThemeConfig.php", {
        method: "POST",
        credentials: "include",
        body: fd
    });
    let json = await response.json();
    return json;
}