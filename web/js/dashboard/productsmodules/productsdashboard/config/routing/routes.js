import { mainscreen } from '../../components/mainscreen/';
import { grid } from '../../components/grid/';
//import { produuctinfo } from '../../components/productinfo/';
const routes = [
    {
        path: '/',
        component: mainscreen,
        meta: { requiresAuth: false },
        children: [
            {
                path: '/',
                component: grid,
                name: 'grid',
                meta: { requiresAuth: false }
            },
            // {
            //     path: '#product/:id',
            //     component: productinfo,
            //     name: 'product',
            //     meta: { requiresAuth: false }
            // }
        ]
    }
];

export default routes;