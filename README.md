

# Spring entities generator
Small tool to generate Java Spring entites with default DTO + mapper (vanilla or mapstruct)

<p>
<img alt="Java : 17" src="https://img.shields.io/badge/Java-17-green.svg" /> <img alt="Spring Boot : 2.7.45" src="https://img.shields.io/badge/Spring%20Boot-2.7.5-green.svg" />
</p>

# How to use

This script takes a definition file as input, and generate files inside the `output` folder.<br />
Just run `php -q seg.php path/to/definition/file`

## Definition file

There are 2 example files provided with the tool in the root folder :
- `definition-example.json`
- `definition-example-complex.json`

### JSON
```
{
  "props":{
    "mapstruct":true,
    "lombok":true,
    "rootPackage":"com.example.demo",
    "package":"domain",
    "spaces":4
  },
  "entities":{
    "MyEntity":{
      "primaryKey":"id",
      "attributes":{
        "id":"long",
        "name":"string"
      }
    }
  }
}
```

### XML

_Comming soon..._

### YAML

_Comming soon_..._