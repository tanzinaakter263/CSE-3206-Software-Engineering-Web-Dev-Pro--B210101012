import React from 'react'
import { useState } from 'react';
import { useEffect } from 'react';
import { apiUrl } from './http';
import ProductImg from '../../assets/images/eleven.jpg';
const FeaturedProducts = () => {

    const [products, setProducts] = useState([]);
        const featuredProducts = async() =>{
           await fetch(apiUrl+'/get-featured-products',{
                method : 'GET',
                headers : {
                    'Content-type' : 'application/json',
                    'Accept' : 'application/json',
                }
            }) .then(res => res.json())
            .then(result => {
 console.log(result)
                setProducts(result.data)
               
            });
        }
        useEffect(() =>{
            featuredProducts()
        },[])
  return (
    <section className='section-2 py-5'>
                       <div className='container'>
                           <h2> Featured Products </h2>
                           <div className='row mt-4'>
                               {
                        products && products.map(product =>{
                            return(
                                <div className='col-md-3 col-6' key={`product-${product.id}`}>
                        <div className='product card border-0'>
                            <div className='card-img'>
                                <img src={product.image_url} alt="" className='w-100' />

                            </div>
                            <div className='card-body pt-3'>
                                <a href="" >{product.title}</a>
                                <div className='price'>

                                    ${product.price} $nbsp;
                                    {
                                        product.compare_price &&  <span className='text-decoration-line-through'>${product.compare_price}</span >
                                    }
                               
                                </div>
                            </div>

                        </div>

                    </div>

                            )
                        })
                    }
       
                           </div>
       
                       </div>
       
                   </section>
      
  )
}

export default FeaturedProducts