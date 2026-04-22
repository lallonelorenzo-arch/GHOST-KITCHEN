import { createBrowserRouter } from "react-router";
import { Root } from "./components/Root";
import { Home } from "./components/pages/Home";
import { SearchChefs } from "./components/pages/SearchChefs";
import { ChefDetail } from "./components/pages/ChefDetail";
import { GhostKitchens } from "./components/pages/GhostKitchens";
import { GhostKitchenDetail } from "./components/pages/GhostKitchenDetail";
import { Booking } from "./components/pages/Booking";
import { Dashboard } from "./components/pages/Dashboard";
import { Profile } from "./components/pages/Profile";
import { NotFound } from "./components/pages/NotFound";

export const router = createBrowserRouter([
  {
    path: "/",
    Component: Root,
    children: [
      { index: true, Component: Home },
      { path: "chefs", Component: SearchChefs },
      { path: "chefs/:id", Component: ChefDetail },
      { path: "ghost-kitchens", Component: GhostKitchens },
      { path: "ghost-kitchens/:id", Component: GhostKitchenDetail },
      { path: "booking/:type/:id", Component: Booking },
      { path: "dashboard", Component: Dashboard },
      { path: "profile", Component: Profile },
      { path: "*", Component: NotFound },
    ],
  },
]);
