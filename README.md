<p>
<img alt="Java : 17" src="https://img.shields.io/badge/Java-17-blue.svg" /> <img alt="Spring Boot : 2.7.45" src="https://img.shields.io/badge/Spring%20Boot-2.7.5-blue.svg" /> <img alt="Latest stable : 1.1.0" src="https://img.shields.io/badge/Latest%20stable-1.1.0-green.svg" />
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
| repositories | No | `Repositories` object | The configuration for repositories generation
| spaces  | Yes  | `Int` | Number of spaces for one indentation  |

#### 'props'.'repositories' section

| Property      | Mandatory     | Possible values | Description    | 
| ------------- | ------------- | ------------- | ------------- |
| generate  | Yes  | `true`/`false` | Enable/disable generation (same as not put the section)  |
| all  | Yes  | `true`/`false`  | Flag for generation of repositories for all entities   |
| package  | Yes  | Any `String`  | Name of the package for repositories   |

#### 'entities'.'entityXX' section

| Property      | Mandatory     | Possible values | Description    | 
| ------------- | ------------- | ------------- | ------------- |
| primaryKey  | No  | Any `String` | Name of the key that will be used as primary key for entity  |
| attributes  | Yes  | An `Array` of attributes  | Array used to define all the attributes of an entity   |

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
    "repositories":{
      "generate":true,
      "all":true,
      "package":"repository"
    },
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
    <repositories>
      <generate>true</generate>
      <all>true</all>
      <package>repository</package>
    </repositories>
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
  repositories:
    generate: true
    all: true
    package: "repository"
  spaces: 4
entities:
  MyEntity:
    primaryKey: "id"
    attributes:
      id: "long"
      name: "string"
```