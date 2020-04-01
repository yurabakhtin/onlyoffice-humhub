const fs = require("fs");
const path = require("path");

var readme = fs.readFileSync("README.md").toString();
var regex = /^([#]+) (.*)/gm;

var mappings = {
    "README.md": ["# HumHub ONLYOFFICE integration plugin", "## Features"],
    "INSTALLATION.md": ["## Installing ONLYOFFICE Document Server", "## Installing HumHub ONLYOFFICE integration plugin", "## Configuring HumHub CONLYOFFICE integration plugin"],
    "DEVELOPER.md": ["## How it works"],
    "MANUAL.md": []
}
var results = {};

var last = null;
while ((res = regex.exec(readme)) !== null) {
    if (last) {
        last.lastIndex = res.index;
    }
    last = {
        index: res.index
    }
    results[res[0]] = last;
}

for (var key in mappings) {
    if (!mappings[key].length) continue;

    var file = path.join(__dirname, "docs", key);
    var content = "";
    for (var i = 0; i < mappings[key].length; i++) {
        var title = mappings[key][i];
        var obj = results[title];
        var chunk = obj.lastIndex ? readme.substr(obj.index, obj.lastIndex - obj.index) : readme.substr(obj.index);
        content += chunk;
    }
    fs.writeFileSync(file, content);
}