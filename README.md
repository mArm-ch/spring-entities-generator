<p>
<img alt="Java : 17" src="https://img.shields.io/badge/Java-17-green.svg" /> <img alt="Spring Boot : 2.7.45" src="https://img.shields.io/badge/Spring%20Boot-2.7.5-green.svg" />
</p>

# Spring entities generator
Small tool to generate Java Spring entites with default DTO + mapper (vanilla or mapstruct)

# How to use

This script takes a definition file as input, and generate files inside the `output` folder.<br />
Just run `php -q seg.php path/to/definition/file`

## Requirements
- `PHP 7.2.34`
- Yaml php extension enabled

## Definition file

### Definition file properties

#### 'props' section

| Property      | Mandatory     | Possible values | Description    | 
| ------------- | ------------- | ------------- | ------------- |
| mapstruct  | Yes  | `true`/`false` | Flag for mapstruct support   |
| lombok  | Yes  | `true`/`false` | Flag for mapstruct support   |
| rootPackage  | Yes  | Any `String` | Name of the root package that will contains entities package   |
| package  | Yes  | Any `String` | Name of the package where the entities will be created   |
| spaces  | Yes  | `Int` | Number of spaces for one indentation  |


### Examples

There are 6 example files provided with the tool in the `./definition` folder :
- `definition-example.json`
- `definition-example-complex.json`
- `definition-example.yaml`
- `definition-example-complex.yaml`
- `definition-example.xml`
- `definition-example-complex.xml`

#### JSON
```json
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

#### XML

```xml
<?xml version='1.0' encoding='UTF-8'?>
<definition>
  <props>
    <mapstruct>true</mapstruct>
    <lombok>true</lombok>
    <rootPackage>com.example.demo</rootPackage>
    <package>domain</package>
    <spaces>4</spaces>
  </props>
  <entities>
    <MyEntity>
      <primaryKey>id</primaryKey>
      <attributes>
        <id>long</id>
        <name>string</name>
      </attributes>
    </MyEntity>
  </entities>
</definition>
```

#### YAML

```yaml
props:
  mapstruct: true
  lombok: true
  rootPackage: "com.example.demo"
  package: "domain"
  spaces: 4
entities:
  MyEntity:
    primaryKey: "id"
    attributes:
      id: "long"
      name: "string"
```