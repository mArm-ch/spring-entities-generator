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
| mapstruct  | Yes  | `true`/`false` | Flag for Mapstruct support   |
| lombok  | Yes  | `true`/`false` | Flag for Lombok support   |
| rootPackage  | Yes  | Any `String` | Name of the root package that will contains entities package   |
| package  | Yes  | Any `String` | Name of the package where the entities will be created   |
| spaces  | Yes  | `Int` | Number of spaces for one indentation  |

#### 'entities'.'entityXX' section

| Property      | Mandatory     | Possible values | Description    | 
| ------------- | ------------- | ------------- | ------------- |
| primaryKey  | No  | Any `String` | Name of the key that will be used as primary key for entity  |
| attributes  | Yes  | An `Array` of attribues  | Array used to define all the attributes of an entity   |

Inside the `attributes` key, you need to set each attributes of the entity.<br />
Refere to the examples config corresponding to your file format.

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