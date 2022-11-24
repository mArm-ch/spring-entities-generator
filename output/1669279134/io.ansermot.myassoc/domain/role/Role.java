package io.ansermot.myassoc.domain.role;


@Entity
@Data
@NoArgsConstructor
@AllArgsContructor
public class Role {
    @Id
    @GeneratedValue(strategy = GenerationType.AUTO)
    private Long id;
    private String name;
}