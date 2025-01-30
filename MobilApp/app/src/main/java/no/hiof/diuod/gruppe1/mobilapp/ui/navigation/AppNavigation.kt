package no.hiof.diuod.gruppe1.mobilapp.ui.navigation

import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.padding
import androidx.compose.material3.Scaffold
import androidx.compose.runtime.Composable
import androidx.compose.ui.Modifier
import androidx.navigation.compose.NavHost
import androidx.navigation.compose.composable
import androidx.navigation.compose.rememberNavController
import no.hiof.diuod.gruppe1.mobilapp.ui.navigation.navBars.BottomNavBar
import no.hiof.diuod.gruppe1.mobilapp.ui.navigation.navBars.TopBar
import no.hiof.diuod.gruppe1.mobilapp.ui.navigation.navBars.getCurrentScreen
import no.hiof.diuod.gruppe1.mobilapp.ui.screens.HomeScreen
import no.hiof.diuod.gruppe1.mobilapp.ui.screens.LoginScreen
import no.hiof.diuod.gruppe1.mobilapp.ui.viewModels.LoginViewModel

@Composable
fun AppNavigation() {
    val navController = rememberNavController()
    val currentScreen = getCurrentScreen(navController)

    Scaffold(
        topBar = {
            TopBar(navController)
        },
        bottomBar = {
            BottomNavBar(navController)
        },
        modifier = Modifier.fillMaxSize()
    ) { innerPadding ->

        NavHost(
            navController = navController,
            startDestination = AppScreens.LOGIN.name,
            modifier = Modifier.padding(innerPadding)
        ) {

            composable(AppScreens.HOME.name) {
                HomeScreen()
            }

            composable(AppScreens.LOGIN.name) {
                LoginScreen(navController = navController)
            }

        }

    }

}