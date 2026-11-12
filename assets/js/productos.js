/**
 * Productos de la Tienda — Experiencia Sensitiva
 *
 * Para agregar un producto, copiá un bloque { ... }, pegalo en la lista y completá:
 *   nombre:      nombre corto del producto
 *   categoria:   tiene que coincidir con una de las CATEGORIAS de abajo
 *   descripcion: una o dos frases, en el tono de la marca
 *   imagen:      link a la foto (podés copiar la de Mercado Libre: clic derecho en la foto → "Copiar dirección de imagen")
 *                o una foto subida a assets/img/tienda/. Si la dejás vacía, se muestra un ícono.
 *   link:        tu link de afiliado de Mercado Libre
 *   ejemplo:     borralo (o poné false) cuando el producto sea real
 */
window.CATEGORIAS = ["Velas", "Inciensos", "Cristales", "Aceites"];

window.PRODUCTOS = [
  {
    nombre: "Vela de soja Vainilla y Café",
    categoria: "Velas",
    descripcion: "Cera de soja en vaso de vidrio y mecha de madera que crepita suave. Un aroma cálido a vainilla y café para darle a tu casa un clima de pausa.",
    imagen: "assets/img/tienda/vela-lotus-zen-vainilla-cafe.jpg",
    link: "https://articulo.mercadolibre.com.ar/MLA-3295507706?attributes=SCENT:VmFpbmlsbGEgeSBjYWbDqQ==,COLOR_SECONDARY_COLOR:VmFpbmlsbGEgeSBjYWbDqQ=="
  },
  {
    nombre: "Sahumerios de palo santo",
    categoria: "Inciensos",
    descripcion: "Para limpiar el ambiente y acompañar una pausa, una meditación o el final del día.",
    imagen: "",
    link: "#",
    ejemplo: true
  },
  {
    nombre: "Cuarzo rosa",
    categoria: "Cristales",
    descripcion: "Una piedra para tener cerca y recordarte que también merecés cuidado.",
    imagen: "",
    link: "#",
    ejemplo: true
  },
  {
    nombre: "Aceite de masaje de almendras",
    categoria: "Aceites",
    descripcion: "Para regalarte un automasaje o prolongar en casa la sensación de una sesión.",
    imagen: "",
    link: "#",
    ejemplo: true
  }
];
