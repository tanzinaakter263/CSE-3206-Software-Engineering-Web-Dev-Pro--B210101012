export const apiUrl='http://localhost:8000/api'
export const adminToken=()=>{
    const data=JSON.parse(localStorage.getItem('adminInfo'))
    return data.token;
}


export const userToken=()=>{
    const data=JSON.parse(localStorage.getItem('userInfo'))
    return data.token;
}

export const STRIPE_PUBLIC_KEY='pk_test_51Tw1UFQOfoXqqOCe6HQpbnNLp1fxAVIbrM46h9AmRaaNOo44EQOIjCoQrr4fWYaS07o0EVZ5yzjVDhDFSoFNkS4e003OoCZfUS'