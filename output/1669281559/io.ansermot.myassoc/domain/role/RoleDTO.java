package io.ansermot.myassoc.domain.role;

public class RoleDTO {
    private Long id;
    private String name;

    public function getId() {
        return this.id;
    }
    public function setId(Long id) {
        this.id = id;
    }

    public function getName() {
        return this.name;
    }
    public function setName(String name) {
        this.name = name;
    }
}