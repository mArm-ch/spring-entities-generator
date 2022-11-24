package io.ansermot.myassoc.domain.role;

import javax.persistence.Entity;
import javax.persistence.GeneratedValue;
import javax.persistence.Id

import static javax.persistence.GenerationType.AUTO;

@Entity
public class Role {
    @Id
    @GeneratedValue(strategy = AUTO)
    private Long id;
    private String name;

    public Role() {
        this.id = null;
        this.name = null;
    }

    public Role(Long id, String name) {
        this.id = id;
        this.name = name;
    }
}