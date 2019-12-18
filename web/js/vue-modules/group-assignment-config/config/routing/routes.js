import { generalconfig } from '../../components/generalconfig/';
import { rulesdetails } from '../../components/rulesdetails/';

const routes = [
    {
        path: '/',
        name: 'index',
        component: generalconfig,
        meta: { requiresAuth: false }
    },
    {
        path: 'rulesdetails/:id',
        component: rulesdetails,
        name: 'rulesdetails',
        meta: { requiresAuth: false }
    }
];

export default routes;