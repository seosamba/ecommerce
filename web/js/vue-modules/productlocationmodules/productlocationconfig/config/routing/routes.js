import { productlocationconfig } from '../../components/productlocationconfig/';

const routes = [
    {
        path: '/',
        name: 'index',
        component: productlocationconfig,
        meta: { requiresAuth: false }
    }
];

export default routes;