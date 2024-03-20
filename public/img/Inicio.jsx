import useQuisco from '../hooks/useQuiosco'
import Producto from '../components/Producto'
import ProductosTop from '../components/ProductosTop'

export default function Inicio() {

  const { categoriaActual, productoQuery } = useQuisco()


  if (!productoQuery) return 'cargando...';

  const productos = productoQuery?.data.filter(producto => producto.categoria_id === categoriaActual.id && producto.disponible === 1)

  const anchoPantalla = window.innerWidth;

  return (

    <div className=' mb-40 overflow-hidden overflow-y-scroll md:overflow-y-hidden' >
      {productoQuery ? (
        <>
          {anchoPantalla < 767 && <ProductosTop />}

          <h1 className='text-4xl m-4 md:m-0 font-black'>{categoriaActual.nombre}</h1>
          <p className=' hidden m-1 md:flex text-2xl my-10'>
            Elige y personaliza tu pedido a continuación.
          </p>

          <div className='  grid gap-1 md:gap-4 grid-cols-2 md:grid-cols-1 xl:grid-cols-3'>
            {productos.map(producto => (
              <Producto
                key={producto.imagen}
                producto={producto}
                botonAgregar={true}
              />
            ))}
          </div>
        </>
      ) : null}
    </div>
  )
}
