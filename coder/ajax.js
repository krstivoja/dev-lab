document.addEventListener("DOMContentLoaded", () => {
    Alpine.data("coder", () => ({
        files: [],
        fileContent: "",
        selectedFile: null,
        init() {
            this.fetchFilesList();
        },
        fetchFilesList() {
            fetch(ajaxData.ajaxurl, {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded",
                },
                body: new URLSearchParams({
                    action: "coder_fetch_files_list",
                }),
            })

            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    this.files = data.data;
                } else {
                    console.error("Error fetching files list:", data.data);
                }
            })

            .catch((error) => {
                console.error("Error fetching files list:", error);
            });

        },
        fetchFileContent(file) {
            this.selectedFile = file;
            fetch(ajaxData.ajaxurl, {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded",
                },
                body: new URLSearchParams({
                    action: "coder_fetch_file_content",
                    file: file,
                }),
            })
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    this.fileContent = data.data;
                } else {
                    console.error("Error fetching file content:", data.data);
                }
            })
            
            .catch((error) => {
                console.error("Error fetching file content:", error);
            });
        },
    }));

    // Move the Alpine.start() call inside the event listener to ensure it's called after the data function is defined.
    Alpine.start();
});
