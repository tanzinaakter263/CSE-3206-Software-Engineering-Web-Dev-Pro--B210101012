import React,{useMemo,useState,useRef, useEffect} from 'react'
import Layout from '../../common/Layout'
import { Link ,useNavigate, useParams} from 'react-router-dom'
import Sidebar from '../../common/Sidebar'
import { useForm } from 'react-hook-form'
import { toast } from 'react-toastify'
import JoditEditor from 'jodit-react'
import { apiUrl,adminToken } from '../../common/http'


const Edit = ({ placeholder }) => {
  const editor = useRef(null);
  const [content, setContent] = useState('');
  const [disable, setDisable] = useState(false)
  const [categories, setCategories] = useState([])
  const [brands, setBrands] = useState([])
  const [sizesChecked, setSizesChecked] = useState([])
   const [sizes, setSizes] = useState([])
   const [productImages,setProductImages] =useState([])
  //const [galleryImages, setGalleryImages] = useState([])
  const navigate = useNavigate();
  const params =useParams();
  const {id} =useParams();
   const config = useMemo(
      () => ({
        readonly: false, 
        placeholder: placeholder || ''
      }),
      [placeholder]
    );
  const {
      register,
      handleSubmit,
      watch,
      setError,
      reset,
      formState: { errors },
    } = useForm({
      defaultValues: async()=> {
         const res = await fetch(`${apiUrl}/products/${params.id}`, {
            method: 'GET',
            headers: {
              'Content-type': 'application/json',
              'Accept': 'application/json',
              'Authorization': `Bearer ${adminToken()}`
            }
      
          })
          .then(res => res.json())
          .then(result =>{
          
            setProductImages(result.data.product_images)
            setSizesChecked(result.productSizes)
            reset({
              title: result.data.title,
              category: result.data.category_id,
              brand: result.data.brand_id,
              sku: result.data.sku,
              qty: result.data.qty,
              short_description: result.data.short_description,
              description: result.data.description,
              price: result.data.price,
              compare_price: result.data.compare_price,
               barcode: result.data.barcode,
              status: result.data.status,
              is_featured: result.data.is_featured,
            })
           // console.log(result)
            //setBrands(result.data)
          })
      }
 });
     const saveProduct = async (data) => {
    
        const formData = { ...data, "description": content  }
    
        setDisable(true);
    
        const res = await fetch(`/api/products/${params.id}`, {
          method: 'POST',
          headers: {
            'Content-type': 'application/json',
            'Accept': 'application/json',
            'Authorization': `Bearer ${adminToken()}`
          },
          body: JSON.stringify(formData)
    
    
        })
        .then(res => res.json())
          .then(result => {
            setDisable(false);
    
            if (result.status == 200) {
              toast.success(result.message);
              navigate('/admin/products')
    
            } else {
              const formErrors = result.errors;
              Object.keys(formErrors).forEach((field) => {
                setError(field, { message: formErrors[field][0] });
              })
    
            }
    
          })
      }
      const fetchCategories = async () => {
      
          const res = await fetch(`${apiUrl}/categories`, {
            method: 'GET',
            headers: {
              'Content-type': 'application/json',
              'Accept': 'application/json',
              'Authorization': `Bearer ${adminToken()}`
            }
      
          })
            .then(res => res.json())
            .then(result => {
      
              setCategories(result.data)
      
            })
      
        }
      
      
        const fetchBrands = async () => {
      
          const res = await fetch(`${apiUrl}/brands`, {
            method: 'GET',
            headers: {
              'Content-type': 'application/json',
              'Accept': 'application/json',
              'Authorization': `Bearer ${adminToken()}`
            }
      
          })
            .then(res => res.json())
            .then(result => {
              setBrands(result.data)
      
            })
      
        }

         const fetchSizes = async () => {
      
          const res = await fetch(`${apiUrl}/sizes`, {
            method: 'GET',
            headers: {
              'Content-type': 'application/json',
              'Accept': 'application/json',
              'Authorization': `Bearer ${adminToken()}`
            }
      
          })
            .then(res => res.json())
            .then(result => {
             // console.log(result);
              setSizes(result.data)
      
            })
      
        }


              const handleFile = async (e) => {
    const file = e.target.files[0];
    if (!file) return; 

    const formData = new FormData();
    formData.append("image", file);
    formData.append("product_id", id);

    setDisable(true);

    try {
        const res = await fetch(`${apiUrl}/save-product-image`, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${adminToken()}`
            },
            body: formData
        });

        const result = await res.json();

        if (res.status === 200) {
          const newImage ={
            id: result.image_id,
            image:result.imagePath,
            image_url: `${apiUrl}/uploads/products/small/${result.imagePath}`
          };
          
            setProductImages([...productImages, newImage]); 
                toast.success("Image Uploaded!");
        } else {
          
            const errorMsg = result.message || result.errors?.image[0] || "Something went wrong";
            toast.error(errorMsg);
        }

    } catch (error) {
        console.error("Error uploading image:", error);
        toast.error("Server connection failed");
    } finally {
        setDisable(false);
        e.target.value = ""; 
    }
};

 const changeImage = async(image) =>{
  //const formData=new FormData();
  //formData.append("image",image);
  const res = await fetch(`${apiUrl}/change-product-default-image`, {
            method: 'POST',
            headers: {
              'Content-type': 'application/json',
              //'Accept': 'application/json',
              'Authorization': `Bearer ${adminToken()}`
            }, body:JSON.stringify({
              product_id:params.id,
              image:image
            })
      
          });
            //.then(res => res.json())
            //.then(result => {
              const result =await res.json();
              console.log(result);
              if(res.ok){
                toast.success(result.message)
              } 
  
      }
 const deleteImage = async (id) => {
  if(confirm("Are you sure you want to delete image?")){
    const res = await fetch(`${apiUrl}/delete-product-image/${id}`, {
            method: 'DELETE',
            headers: {
              //'Content-type': 'application/json',
              'Accept': 'application/json',
              'Authorization': `Bearer ${adminToken()}`
            }
      
          });
           const result = await res.json();
           // .then(result => {
             if(result.status==200){
               const newProductImages =productImages.filter(productImages => productImages.id !=id)
               setProductImages(newProductImages)
              toast.success(result.message)
             } else{
              toast.error(result.message)
             }
      
            }

  }

          
   
        
        
          useEffect(() =>{
            fetchCategories();
            fetchBrands();
            fetchSizes();

          },[] )
  return (
   <Layout>
      <div className='container'>
        <div className='row'>
          <div className="d-flex justify-content-between mt-5 pb-3">
            <h4 className="h4 pb-0 mb-0"> Products / Edit</h4>
            <Link to="/admin/products" className="btn btn-primary">Back</Link>
          </div>
          <div className='col-md-3'>
            <Sidebar />

          </div>
          <div className='col-md-9'>
            <form onSubmit={handleSubmit(saveProduct)}>
              <div className='card shadow'>
                <div className='card-body p-4'>
                  <div className='mb-3'>
                    <label htmlFor="" className='form-label'>
                      Title
                    </label>
                    <input
                      {
                      ...register('title', {
                        required: 'The title field is required'
                      })
                      }
                      type="text"
                      className={`form-control ${errors.title && 'is-invalid'}`}
                      placeholder='Title' />
                    {
                      errors.title &&
                      <p className='invalid-feedback'>{errors.title?.message}</p>
                    }

                  </div>
                  <div className='row'>
                    <div className='col-md-6'>
                      <div className='mb-3'>
                        <label className='form-label' htmlFor="">Category</label>
                        <select
                          {
                          ...register('category', {
                            required: 'Please select a Category'
                          })
                          }
                          className={`form-control ${errors.category && 'is-invalid'}`}>
                          <option value="">Select a Category </option>
                          {
                            categories && categories.map((category) => {
                              return (
                                <option key={`caterory-${category.id}`} value={category.id}>{category.name}</option>
                              )

                            })
                          }
                        </select>
                        {
                          errors.category &&
                          <p className='invalid-feedback'>{errors.category?.message}</p>
                        }

                      </div>
                    </div>

                    <div className='col-md-6'>
                      <div className='mb-3'>
                        <label className='form-label' htmlFor="">Brand</label>
                        <select
                          {
                          ...register('brand')
                          }
                          className='form-control'>
                          <option value="">Select a Brand </option>
                          {
                            brands && brands.map((brand) => {
                              return (
                                <option key={`brand-${brand.id}`} value={brand.id}>{brand.name}</option>
                              )

                            })
                          }
                        </select>

                      </div>
                    </div>


                  </div>
                  <div className='mb-3'>
                    <label htmlFor="" className='form-label'>Short Description</label>
                    <textarea
                      {
                      ...register('short_description')
                      }

                      className='form-control' placeholder='Short Description' rows={3}></textarea>
                  </div>
                  <div className='mb-3'>
                    <label htmlFor="" className='form-label'>Description</label>

                    <JoditEditor
                      ref={editor}
                      value={content}
                      config={config}
                      tabIndex={1} // tabIndex of textarea
                      onBlur={newContent => setContent(newContent)} // preferred to use only this option to update the content for performance reasons

                    />
                  </div>

                  <h3 className="py-3 border-bottom mb-3">Pricing</h3>
                  <div className='row'>
                    <div className='col-md-6'>
                      <div className='mb-3'>
                        <label htmlFor="" className='form-label'>Price</label>
                        <input
                          {
                          ...register('price', {
                            required: 'The price field is required'
                          })
                          }
                          className={`form-control ${errors.price && 'is-invalid'}`}

                          type="text" placeholder='Price' />
                        {
                          errors.price &&
                          <p className='invalid-feedback'>{errors.price?.message}</p>
                        }


                      </div>

                    </div>

                    <div className='col-md-6'>
                      <div className='mb-3'>
                        <label htmlFor="" className='form-label'>Compare Price</label>
                        <input
                          {
                          ...register('compare_price')
                          }

                          type="text" placeholder=' Compare Price' className='form-control' />


                      </div>

                    </div>

                  </div>
                  <h3 className="py-3 border-bottom mb-3">Inventory</h3>
                  <div className='row'>
                    <div className='col-md-6'>
                      <div className='mb-3'>
                        <label htmlFor="" className='form-label'>SKU</label>
                        <input
                          {
                          ...register('sku', {
                            required: 'The sku field is required'
                          })
                          }
                          className={`form-control ${errors.price && 'is-invalid'}`}
                          type="text" placeholder='Sku' />
                        {
                          errors.sku &&
                          <p className='invalid-feedback'>{errors.sku?.message}</p>
                        }


                      </div>

                    </div>

                    <div className='col-md-6'>
                      <div className='mb-3'>
                        <label htmlFor="" className='form-label'>Barcode</label>
                        <input
                          {
                          ...register('barcode')
                          }

                          type="text" placeholder=' arcode' className='form-control' />


                      </div>

                    </div>

                  </div>

                  <div className='row'>
                    <div className='col-md-6'>
                      <div className='mb-3'>
                        <label htmlFor="" className='form-label'>QTY</label>
                        <input
                          {
                          ...register('qty')
                          }

                          type="text" placeholder='Qty' className='form-control' />


                      </div>

                    </div>

                    <div className='col-md-6'>
                      <div className='mb-3'>
                        <label htmlFor="" className='form-label'>
                          Status
                        </label>
                        <select
                          {
                          ...register('status', {
                            required: 'Please select a status'
                          })
                          }

                          className={`form-control ${errors.status && 'is-invalid'}`}
                        >
                          <option value="">Select  a Status</option>
                          <option value="1">Active</option>
                          <option value="0">Block</option>
                        </select>
                        {
                          errors.status &&
                          <p className='invalid-feedback'>{errors.status?.message}</p>
                        }

                      </div>


                    </div>

                  </div>


                  <div className='mb-3'>
                    <label htmlFor="" className='form-label'>
                      Featured
                    </label>
                    <select
                      {
                      ...register('is_featured', {
                        required: 'This field is required'
                      })
                      }

                      className={`form-control ${errors.is_featured && 'is-invalid'}`}
                    >

                      <option value="yes">Yes</option>
                      <option value="no">No</option>
                    </select>
                    {
                      errors.status &&
                      <p className='invalid-feedback'>{errors.status?.message}</p>
                    }




                  </div>
                  <div className='mb-3'>
                    <label htmlFor="" className='form-label'>
                      Sizes
                    </label>


                    {
                      sizes && sizes.map(size=>{
                        return (
                          <div className="form-check-inline ps-2" key={`psize=${size.id}`}>
                      <input 
                      {
                        ...register("sizes")
                      }
                      checked={sizesChecked.includes(size.id)}
                      onChange={(e)=>{
                        if(e.target.checked){
                          setSizesChecked([...sizesChecked,size.id])

                        } else{
                          setSizesChecked(sizesChecked.filter(sid => size.id !=sid))


                        }
                      }}
                      className="form-check-input" type="checkbox"  value={size.id} id={`size={size.id}`}/>
                      <label className="form-check-label ps-2" htmlFor="{`size={size.id}`}">
                        {size.name}
                        </label>
                        
                    

                    </div>

                        )
                      })
                    }
                    

                  </div>


                  <h3 className="py-3 border-bottom mb-3">Gallery</h3>
                  <div className='mb-3'>
                    <label htmlFor="" className='form-label'>Image</label>
                    <input
                      onChange={handleFile}
                      type="file" className='form-control' />

                  </div>
                  <div className="row mt-3">
                    {
                      productImages && productImages.map((productImage, index) => (
                        <div className="col-md-3 mb-3" key={index}>
                          <div className="card shadow-sm">
                            <img
                              src={productImage.image_url?productImage.image_url:`${apiUrl}/uploads/products/small/${productImage.image}`}
                              className="card-img-top w-100"
                              alt="Product Image"
                            />

                            <div className="card-body text-center p-2">
                              <button
                                type='button'
                                onClick={() => deleteImage(productImage.id)}
                                className="btn btn-danger btn-sm w-100 mt-3"
                              >
                                Delete
                              </button>
                              <button
                                type='button'
                                onClick={() => changeImage(productImage.image)}
                                className="btn btn-secondary btn-sm w-100 mt-3"
                              >
                                Set as default
                              </button>
                            </div>
                          </div>
                        </div>
                      ))
                    }
                  </div>

                </div>



              </div>
              <button
                disabled={disable}
                type='submit' className='btn btn-primary mt-3 mb-5'>Update</button>
            </form>

          </div>
        </div>

      </div>
    </Layout>
  )
}

export default Edit